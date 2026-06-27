<?php

namespace Modules\Vendor\Services;

use Modules\Vendor\Repositories\VendorRepository;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Illuminate\Support\Facades\Storage;

class VendorService
{
    protected $vendorRepository;

    public function __construct(VendorRepository $vendorRepository)
    {
        $this->vendorRepository = $vendorRepository;
    }

    /**
     * Get paginated vendors
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedVendors(int $perPage = 15)
    {
        return $this->vendorRepository->paginate($perPage);
    }

    /**
     * Create new vendor
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     * @throws InvalidArgumentException
     */
    public function createVendor(array $data)
    {
        return $this->vendorRepository->create($data);
    }

    /**
     * Update vendor
     *
     * @param int $id
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     * @throws InvalidArgumentException
     */
    public function updateVendor(int $id, array $data)
    {
        $validator = Validator::make($data, [
            'store_name' => 'sometimes|required|string|max:255',
            'store_logo' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'store_type' => 'nullable|string|max:50',
            'store_id' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }


        $vendor = $this->vendorRepository->update($id, $data);

        if (!$vendor) {
            throw new InvalidArgumentException('Failed to update vendor');
        }

        return $vendor;
    }

    /**
     * Delete vendor
     *
     * @param int $id
     * @return bool
     */
    public function deleteVendor(int $id)
    {
        $vendor = $this->vendorRepository->show($id);
        
        if ($vendor) {
            // Delete associated files
            if ($vendor->store_logo) {
                Storage::disk('public')->delete($vendor->store_logo);
            }
            if ($vendor->store_id) {
                Storage::disk('public')->delete($vendor->store_id);
            }
            
            return $this->vendorRepository->delete($id);
        }
        
        return false;
    }

    /**
     * Get vendor by ID
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getVendorById(int $id)
    {
        return $this->vendorRepository->show($id);
    }


    public function getVendorByUserId(int $id)
    {
        return $this->vendorRepository->showByUser($id);
    }

    
    /**
     * Handle file upload
     *
     * @param mixed $file
     * @param string $path
     * @return string
     */
    protected function handleFileUpload($file, $path)
    {
        if ($file) {
            return $file->store($path, 'public');
        }
        return null;
    }

    /**
     * Get vendor statistics
     *
     * @return array
     */
    public function getVendorStatistics()
    {
        $vendors = $this->vendorRepository->all();
        
        return [
            'total_vendors' => $vendors->count(),
            'waiting_approve' => $vendors->where('status', 'Waiting Approve')->count(),
            'active_vendors' => $vendors->where('status', 'Active')->count(),
            'rejected_vendors' => $vendors->where('status', 'Rejected')->count(),
        ];
    }
}