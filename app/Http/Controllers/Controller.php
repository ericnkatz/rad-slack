<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use App\Status;
use App\Graph;
use GuzzleHttp\Client;

use Cache;

class Controller extends BaseController
{
    function getStatuses() {
    	
		$graph = new Graph(['fields' => ['message', 'link', 'updated_time', 'id', 'caption', 'attachments', 'from', 'child_attachments', 'likes{name,link}']]);

		$statuses = $graph->statuses;

		// $statuses->reverse()->each( function($status) {
		// 	$status->sendToSlack();
		// });

	    return $statuses;
    }
}
