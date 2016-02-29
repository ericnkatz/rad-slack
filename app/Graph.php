<?php

namespace App;

use Illuminate\Support\Collection;
use GuzzleHttp\Client;
use App\Status;

class Graph
{

	public $statuses;

    public function __construct(array $attributes = [])
    {

    	$this->feed = $this->getFeed('https://graph.facebook.com/373511866022579/feed', env('FB_ACCESS_TOKEN'), implode(',', $attributes['fields']));

    	$this->statuses = $this->getStatuses();

    }

    public function getFeed($graph_url, $access_token, $fields) {

    	$client = new Client();
		$res = $client->request('GET', $graph_url, ['query' => [
			'fields' => $fields,
			'access_token' => $access_token
		]]);

		$fb_feed = json_decode($res->getBody(), true);

		return new Collection( $fb_feed['data'] );
    }

    public function getStatuses() {
    	return Status::make($this->feed);
    }
}
