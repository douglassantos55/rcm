<?php

namespace App\Http\Controllers;

use App\Http\Requests\RentRequest;
use App\Messenger\Messenger;
use App\Repositories\RentRepository;
use Illuminate\Http\Request;

class RentController extends Controller
{
    /**
     * @var RentRepository
     */
    private $repository;

    /**
     * @var Messenger
     */
    private $messenger;

    public function __construct(RentRepository $repository, Messenger $messenger)
    {
        $this->messenger = $messenger;
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->query('number')) {
            $this->repository->where('number', $request->query('number'));
        }

        if ($request->query('customer')) {
            $this->repository->where('customer_id', $request->query('customer'));
        }

        if ($request->query('start_date')) {
            $this->repository->contains('start_date', $request->query('start_date'));
        }

        return $this->repository
            ->with(['customer'])
            ->orderBy('created_at', 'DESC')
            ->paginate($request->query('page', 1), $request->query('per_page', 50));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RentRequest $request)
    {
        $rent = $this->repository->create($request->input());

        $this->messenger->send([
            'id' => $rent->id,
            'date' => $rent->start_date,
            'pay_date' => null,
            'value' => $rent->total,
        ], 'rent.created');

        return response($rent, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $rent)
    {
        return $this->repository->find($rent);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RentRequest $request, string $rent)
    {
        $updatedEntity = $this->repository->update($rent, $request->input());

        $this->messenger->send([
            'id' => $updatedEntity->id,
            'date' => $updatedEntity->start_date,
            'pay_date' => null,
            'value' => $updatedEntity->total,
        ], 'rent.updated');

        return $updatedEntity;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $rent)
    {
        $this->repository->delete($rent);
        $this->messenger->send($rent, 'rent.deleted');

        return response()->noContent();
    }
}
