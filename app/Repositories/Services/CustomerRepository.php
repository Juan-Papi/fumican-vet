<?php

namespace App\Repositories\Services;

use App\Models\Services\Customer;

class CustomerRepository
{
    public function __construct(protected Customer $model) {}

    public function getAll()
    {
        return $this->model->orderBy('updated_at', 'desc')->paginate();
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $userData)
    {
        return $this->model->create($userData);
    }

    public function update(array $data, $id)
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function search()
    {
        $search = request('search');
        return Customer::where(function ($query) use ($search) {
            $query->whereLike('first_name', "%$search%")
                ->orWhereLike('last_name', "%$search%")
                ->orWhereLike('ci', "%$search%");
        })
            ->select('id', 'first_name', 'last_name', 'ci')
            ->take(5)
            ->get();
    }
}
