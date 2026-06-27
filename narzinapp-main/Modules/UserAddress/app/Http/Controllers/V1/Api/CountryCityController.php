<?php

namespace Modules\UserAddress\Http\Controllers\V1\Api;

use App\Http\Controllers\Controller;
use Modules\Admin\Models\City;
use Modules\Admin\Models\Country;

class CountryCityController extends Controller
{
    public function countries()
    {
        try {
            $countries = Country::with(['cities'])
                ->get();

            return response()->json([
                'status' => true,
                'data' => $countries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function cities($id)
    {
        try {
            $cities = City::where('country_id', $id)
                ->get();

            return response()->json([
                'status' => true,
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }




    public function showCity($id)
    {
        try {
            $city = City::findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $city
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }


    public function showCountry($id)
    {
        try {
            $country = Country::with(['cities'])
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $country
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
