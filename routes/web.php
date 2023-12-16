<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\JsonResponse;

Route::get('/', function () {
    return view('map');
});

Route::get('/get_data', function () {

    $collection = app('mongoCollection');

    // Create an index on the "city" field
    $collection->createIndex(['city' => 1]);

    $pipeline = [
        ['$match' => ['city' => ['$exists' => true, '$ne' => '']]],
        ['$project' => [
            'city' => ['$trim' => ['input' => '$city']],
            'latitude' => 1,
            'longitude' => 1
        ]],
        ['$match' => ['city' => ['$ne' => '']]],
        ['$group' => [
            '_id' => '$city',
            'latitude' => ['$first' => '$latitude'],
            'longitude' => ['$first' => '$longitude']
        ]],
        ['$project' => [
            '_id' => 0,
            'city' => '$_id',
            'latitude' => 1,
            'longitude' => 1
        ]]
    ];

    $result = $collection->aggregate($pipeline);

    $data = iterator_to_array($result);

    // Convert the data to a list of lists
    $listOfLists = [];
    foreach ($data as $item) {
        $listOfLists[] = [$item['city'], $item['latitude'], $item['longitude']];
    }

    return response()->json($listOfLists);
});
