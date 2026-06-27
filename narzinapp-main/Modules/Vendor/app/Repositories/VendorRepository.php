<?php

namespace Modules\Vendor\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Vendor\Models\Vendor;

class VendorRepository
{
    protected $model;

    public function __construct(Vendor $model)
    {
        $this->model = $model;
    }

    /**
     * Get all vendors
     *
     * @return Collection
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Create a new vendor
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing vendor
     *
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function update(int $id, array $data)
    {
        $vendor = $this->model->findOrFail($id);
        $vendor->update($data);
        return $vendor;
    }

    /**
     * Delete a vendor
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    /**
     * Show vendor details
     *
     * @param int $id
     * @return Model
     */
    public function show(int $id)
    {
        return $this->model->findOrFail($id);
    }


    public function showByUser(int $id)
    {
        return $this->model->where('user_id' ,$id);
    }
    

    /**
     * Get vendor for editing
     *
     * @param int $id
     * @return Model
     */
    public function edit(int $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Get paginated vendors
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Search vendors by criteria
     *
     * @param array $criteria
     * @return Collection
     */
    public function search(array $criteria)
    {
        $query = $this->model->query();

        foreach ($criteria as $key => $value) {
            if ($value) {
                $query->where($key, 'LIKE', "%{$value}%");
            }
        }

        return $query->get();
    }
}