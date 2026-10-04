<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\DeductionCategory;
use App\Models\DeductionType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $version = session('manage_version', 'v1');

        $categories = DeductionCategory::with(['types' => function ($query) {
            $query->orderBy('name');
        }])
            ->where('profile_version', $version)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $availableProfiles = DeductionCategory::select('profile_version')->distinct()->pluck('profile_version')->toArray();

        // Ensure at least 'v1' and 'v2' exist in the array to prevent empty states for new installs
        if (!in_array('v1', $availableProfiles)) $availableProfiles[] = 'v1';
        if (!in_array('v2', $availableProfiles)) $availableProfiles[] = 'v2';
        sort($availableProfiles);

        $categoriesCount = $categories->count();
        $activeCategoriesCount = $categories->where('is_active', true)->count();
        $typesCount = $categories->sum(fn ($c) => $c->types->count());
        $excelMappingCount = $categories->sum(fn ($c) => $c->types->whereNotNull('excel_column')->count());

        return view('hr.settings.deductions.index', compact(
            'categories',
            'categoriesCount',
            'activeCategoriesCount',
            'typesCount',
            'excelMappingCount',
            'version',
            'availableProfiles'
        ));
    }

    /**
     * Toggle the manage profile version.
     */
    public function toggleManageProfile(Request $request)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $request->validate(['manage_version' => 'required|string|in:v1,v2']);

        session(['manage_version' => $request->manage_version]);

        return redirect()->route('hr.settings.deductions.index')
            ->with('success', 'Profile switched to '.strtoupper($request->manage_version).'.');
    }

    /**
     * Create a new custom profile version.
     */
    public function createProfile(Request $request)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $request->validate([
            'new_profile_version' => 'required|alpha_dash|max:50'
        ]);

        $newProfile = strtolower($request->new_profile_version);

        // Switch the session to this new profile
        session(['manage_version' => $newProfile]);

        return redirect()->route('hr.settings.deductions.index')
            ->with('success', 'Switched to new empty profile: ' . strtoupper($newProfile) . '. You can now copy a previous schema or build from scratch.');
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
            'profile_version' => 'required|string|in:v1,v2',
        ]);

        $maxSort = DeductionCategory::max('sort_order') ?? 0;

        DeductionCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'sort_order' => $validated['sort_order'] ?? $maxSort + 1,
            'is_active' => true,
            'profile_version' => $validated['profile_version'],
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
     * Copy the entire schema from one profile version to another.
     */
    public function copySchema(Request $request)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $request->validate([
            'source_version' => 'required|string',
            'target_version' => 'required|string|different:source_version',
        ]);

        $sourceVersion = $request->source_version;
        $targetVersion = $request->target_version;

        // Fetch all source categories with their types
        $sourceCategories = DeductionCategory::with('types')
            ->where('profile_version', $sourceVersion)
            ->get();

        if ($sourceCategories->isEmpty()) {
            return redirect()->back()->with('error', "No categories found in source profile ({$sourceVersion}) to copy.");
        }

        DB::transaction(function () use ($sourceCategories, $targetVersion) {
            foreach ($sourceCategories as $sourceCategory) {
                // 1. Replicate the category under the new profile_version
                $newCategory = $sourceCategory->replicate();
                $newCategory->profile_version = $targetVersion;
                $newCategory->slug = $sourceCategory->slug . '-' . strtolower($targetVersion);
                $newCategory->created_at = now();
                $newCategory->updated_at = now();
                $newCategory->save();

                // 2. Replicate all child deduction types under the new category ID
                foreach ($sourceCategory->types as $sourceType) {
                    $newType = $sourceType->replicate();
                    $newType->category_id = $newCategory->id;
                    $newType->code = $sourceType->code . '_' . strtolower($targetVersion);
                    
                    if (in_array('slug', $newType->getFillable())) {
                        $newType->slug = $sourceType->slug . '-' . strtolower($targetVersion);
                    }
                    
                    $newType->created_at = now();
                    $newType->updated_at = now();
                    $newType->save();
                }
            }
        });

        return redirect()->back()->with('success', "Successfully copied all categories and deduction types from " . strtoupper($sourceVersion) . " to " . strtoupper($targetVersion) . ".");
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
