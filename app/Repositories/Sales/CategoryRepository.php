<?php

namespace App\Repositories\Sales;

use App\Models\Sales\Category;

class CategoryRepository
{

    public function getAll()
    {
        return Category::orderBy('updated_at', 'desc')->paginate();
    }

    public function getAllWithoutPaginate()
    {
        return Category::orderBy('updated_at', 'desc')->get();
    }

    public function findById($id)
    {
        return Category::findOrFail($id);
    }

    public function create(array $userData)
    {
        return Category::create($userData);
    }

    public function update($id, array $data)
    {
        return Category::where('id', $id)->update($data);
    }

    public function search()
    {
        $search = request('search');
        return Category::where(function ($query) use ($search) {
            $query->whereLike('name', "%$search%");
        })
            ->take(5)
            ->get();
    }
}
