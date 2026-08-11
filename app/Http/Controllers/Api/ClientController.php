<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends CrudController
{
    protected string $modelClass = Client::class;
    protected bool $tenantScoped = true;

    public function store(StoreClientRequest $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $client = Client::create($this->tenantPayload($request, $request->validated()));
        $this->logAction($user, 'created client', $client, $client->toArray());
        $this->broadcastModelChange($client, 'created');

        return $this->success($client, 'Client created.', 201);
    }

    public function update(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $client = Client::find($id);
        if (!$client) {
            return $this->error('Client not found.', 404);
        }

        $client->fill($this->tenantPayload($request, $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'tax_id' => ['nullable', 'string', 'max:100'],
        ])));
        $client->save();
        $this->logAction($user, 'updated client', $client, $client->toArray());
        $this->broadcastModelChange($client, 'updated');

        return $this->success($client, 'Client updated.');
    }
}
