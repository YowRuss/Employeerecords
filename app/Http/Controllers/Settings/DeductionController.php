<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\DeductionCategory;
use App\Models\DeductionType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class DeductionController extends Controller
{
    /**
     * Display the deduction categories and types settings page.
     */
    public function index()
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $categories = DeductionCategory::with(['types' => function ($query) {
            $query->orderBy('name');
        }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('hr.settings.deductions.index', compact('categories'));
    }

    /**
     * Store a new deduction category.
     */
    public function storeCategory(Request $request)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:deduction_categories,name',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $maxSort = DeductionCategory::max('sort_order') ?? 0;

        DeductionCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'sort_order' => $validated['sort_order'] ?? $maxSort + 1,
            'is_active' => true,
        ]);

        return redirect()->route('hr.settings.deductions.index')
            ->with('success', 'Deduction category created successfully.');
    }

    /**
     * Update an existing deduction category.
     */
    public function updateCategory(Request $request, string $id)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $category = DeductionCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:deduction_categories,name,'.$category->id,
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'sort_order' => $validated['sort_order'] ?? $category->sort_order,
        ]);

        return redirect()->route('hr.settings.deductions.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Delete a deduction category and all its child types.
     */
    public function destroyCategory(string $id)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $category = DeductionCategory::findOrFail($id);

        // Cascade delete child types
        $category->types()->delete();
        $category->delete();

        return redirect()->route('hr.settings.deductions.index')
            ->with('success', 'Category and its deduction types deleted successfully.');
    }

    /**
     * Toggle the is_active flag on a deduction category.
     */
    public function toggleCategory(string $id)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $category = DeductionCategory::findOrFail($id);
        $category->is_active = ! $category->is_active;
        $category->save();

        $status = $category->is_active ? 'activated' : 'deactivated';

        return redirect()->route('hr.settings.deductions.index')
            ->with('success', "Category \"{$category->name}\" {$status} successfully.");
    }

    /**
     * Store a new deduction type under a category.
     */
    public function storeType(Request $request)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:deduction_categories,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:deduction_types,code',
            'excel_column' => 'nullable|string|max:10',
        ]);

        DeductionType::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'code' => $validated['code'],
            'excel_column' => $validated['excel_column'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('hr.settings.deductions.index')
            ->with('success', 'Deduction type created successfully.');
    }

    /**
     * Update an existing deduction type.
     */
    public function updateType(Request $request, string $id)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $type = DeductionType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:deduction_types,code,'.$type->id,
            'excel_column' => 'nullable|string|max:10',
        ]);

        $type->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'excel_column' => $validated['excel_column'] ?? null,
        ]);

        return redirect()->route('hr.settings.deductions.index')
            ->with('success', 'Deduction type updated successfully.');
    }

    /**
     * Toggle the is_active flag on a deduction type.
     */
    public function toggleType(string $id)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $type = DeductionType::findOrFail($id);
        $type->is_active = ! $type->is_active;
        $type->save();

        $status = $type->is_active ? 'activated' : 'deactivated';

        return redirect()->route('hr.settings.deductions.index')
            ->with('success', "Deduction type \"{$type->name}\" {$status} successfully.");
    }

    /**
     * Delete a deduction type.
     */
    public function destroyType(string $id)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $type = DeductionType::findOrFail($id);
        $type->delete();

        return redirect()->route('hr.settings.deductions.index')
            ->with('success', 'Deduction type deleted successfully.');
    }

    /**
     * Enforce HR/Admin access (role_id 2 or 3).
     * Automatically redirects unauthenticated requests to login,
     * and non-HR users to dashboard instead of aborting with a 403 Forbidden error.
     */
    private function authorizeHrAccess(): ?RedirectResponse
    {
        $userId = Session::get('user_id') ?? Auth::id();
        if (! $userId) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $roleId = Session::get('role_id') ?? Auth::user()?->role_id;
        if (! $roleId) {
            $roleId = User::where('id', $userId)->value('role_id');
            if ($roleId) {
                Session::put('role_id', (int) $roleId);
            }
        }

        if (! in_array((int) $roleId, [2, 3], true)) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access. HR privileges required.');
        }

        return null;
    }
}
