<?php

namespace App\Http\Controllers;

use App\Models\RefBarangay;
use App\Models\RefCity;
use App\Models\RefProvince;
use App\Models\RefRegion;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    /**
     * Get all regions.
     */
    public function getRegions(): JsonResponse
    {
        $regions = RefRegion::select('id', 'region_code', 'region_name')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($regions);
    }

    /**
     * Get provinces by region ID.
     */
    public function getProvinces(mixed $regionId): JsonResponse
    {
        $region = RefRegion::find($regionId) ?? RefRegion::where('region_code', $regionId)->first();

        if (! $region) {
            return response()->json([]);
        }

        $provinces = RefProvince::where('region_code', $region->region_code)
            ->select('id', 'province_code', 'province_name', 'region_code')
            ->orderBy('province_name', 'asc')
            ->get();

        return response()->json($provinces);
    }

    /**
     * Get cities/municipalities by province ID.
     */
    public function getCities(mixed $provinceId): JsonResponse
    {
        $province = RefProvince::find($provinceId) ?? RefProvince::where('province_code', $provinceId)->first();

        if (! $province) {
            return response()->json([]);
        }

        $cities = RefCity::where('province_code', $province->province_code)
            ->select('id', 'city_code', 'city_name', 'province_code')
            ->orderBy('city_name', 'asc')
            ->get();

        return response()->json($cities);
    }

    /**
     * Get barangays by city ID.
     */
    public function getBarangays(mixed $cityId): JsonResponse
    {
        $city = RefCity::find($cityId) ?? RefCity::where('city_code', $cityId)->first();

        if (! $city) {
            return response()->json([]);
        }

        $barangays = RefBarangay::where('city_code', $city->city_code)
            ->select('id', 'brgy_code', 'brgy_name', 'city_code')
            ->orderBy('brgy_name', 'asc')
            ->get();

        return response()->json($barangays);
    }
}
