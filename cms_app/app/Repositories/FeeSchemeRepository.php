<?php

namespace App\Repositories;

use App\Model\Admin\fee_scheme;

class FeeSchemeRepository
{
    public function getAll()
    {
        return fee_scheme::latest()->get();
    }

    public function find($id)
    {
        return fee_scheme::findOrFail($id);
    }

    public function create(array $data)
    {
        return fee_scheme::create($data);
    }

    public function update($id, array $data)
    {
        $scheme = $this->find($id);
        $scheme->update($data);
        return $scheme;
    }

    public function delete($id)
    {
        $scheme = $this->find($id);
        return $scheme->delete();
    }
}
