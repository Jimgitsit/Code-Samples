<?php

class Pinterest {
	
	public function getProfile($username) {
		$profile = array();
		
		$page = @file_get_contents("http://www.pinterest.com/$username/");
		if ($page == false) {
			throw new Exception("Bad username: $username");
		}

		$doc = new DOMDocument();
		$doc->loadHTML($page);
		
		$nodes = $doc->getElementsByTagName('meta');
		if ($nodes->length == 0) {
			throw new Exception("Bad username: $username");
		}
		
		$metas = array();
		foreach ($nodes as $node) {
			$name = trim($node->getAttribute('name'));
			if ($name == '') {
				continue;
			}
			
			$metas[$name] = $node->getAttribute('content');
		}
		
		$profile['followers'] = $metas['pinterestapp:followers'];
		$profile['following'] = $metas['pinterestapp:following'];
		$profile['url'] = $metas['og:url'];
		$profile['picture'] = $metas['og:image'];
		$profile['username'] = $username;
		$profile['boardCount'] = $metas['pinterestapp:boards']; // Does not include secret boards
		$profile['pinCount'] = $metas['pinterestapp:pins']; // Does not include secret boards
		
		$finder = new DOMXPath($doc);
		$className = 'boardLinkWrapper';
		$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");

		$profile['boards'] = array();
		foreach ($nodes as $node) {
			$boardTitle = str_replace('More from ', '', $node->getAttribute('title'));
			$boardLink = $node->getAttribute('href');
			
			//$info = $this->getBoardInfo($boardLink);

			$profile['boards'][] = array(
				'title' => $boardTitle, 
				'url' => $boardLink, 
				//'info' => $info
			);
		}
		
		return $profile;
	}
	
	public function getProfileForDB($username) {
		$profile = $this->getProfile($username);
		$profile['last_updated'] = new MongoDate();
		$profile['connected'] = true;
		return $profile;
	}
	
	public function getBoardInfo($url) {
		$info = array();
		
		$metas = get_meta_tags('http://pinterest.com' . $url);
		if ($metas == false) {
			throw new Exception('Board not found: ' . $url);
		}
		
		$info['followers'] = $metas['followers'];
		$info['pins'] = $metas['pinterestapp:pins'];
		$info['picture'] = $metas['og:image'];
		$info['category'] = $metas['pinterestapp:category'];
		
		return $info;
	}

	public function getId($url) {
		return Social_public_model::getUsernameFromUrl($url);;
	}

	public function getPublicProfileForDB($id) {
		$profile = $this->getProfile($id);
		$profile['id'] = $id;
		$profile['last_updated'] = new MongoDate();
		$profile['connected'] = true;
		return $profile;
	}
} 