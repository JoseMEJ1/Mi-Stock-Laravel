<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends CrudController
{
    protected string $modelClass = Client::class;

    public function store(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $client = Client::create($request->validated());
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

        $client->fill($request->validated());
        $client->save();
        $this->logAction($user, 'updated client', $client, $client->toArray());
        $this->broadcastModelChange($client, 'updated');

        return $this->success($client, 'Client updated.');
    }
}
