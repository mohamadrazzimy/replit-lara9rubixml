<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Extractors\NDJSON;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\CrossValidation\Metrics\Accuracy;

class TrainController extends Controller
{
    public function train(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Determine the file type
            $extension = $file->getClientOriginalExtension();

            if ($extension === 'ndjson') {

                $training = Labeled::fromIterator(new NDJSON($file));

                $testing = $training->randomize()->take(10);

                $estimator = new KNearestNeighbors(5);

                $estimator->train($training);

                $predictions = $estimator->predict($testing);

                $metric = new Accuracy();

                $score = $metric->score($predictions, $testing->labels());
            
            }/*if ndjson extension*/

            // Perform any additional operations with the data

            return response()->json([
                'message' => 'File uploaded and processed successfully.',
                'accuracy' =>$score,
            ]);
        }/*if has file*/

        return response()->json([
            'message' => 'No file found.',
        ], 400);
    }
}
