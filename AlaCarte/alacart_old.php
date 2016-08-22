<?php

# DI and Settings Section - can be moved to settings.php
$di = new \Phalcon\DI\FactoryDefault();
	
// This service returns a mongo database at localhost
$di->set('mongo', function() {
  $mongo = new MongoClient();
  return $mongo->selectDb('test');
}, true);

$di->set('collectionManager', function(){
  return new Phalcon\Mvc\Collection\Manager();
}, true);


# DataBase Section
class Content extends \Phalcon\Mvc\Collection {
 

}

/**
* 
*/
class App extends Phalcon\Mvc\Micro {
  
  public function render_Root() {
    $content = "<h1>Welcome to A la Carte!</h1><p>Time for some custom content :)</p>";

    $content = Content::findFirst();
    App::renderJSONP($content);



    //App::render($content);
  }

  public function test() {
    $params = array('triger' => 'member');

    $content = Content::findFirst();

    $response = array();
    $response['target_id'] = $content->target_id;
    //$response['target_id'] = 'div.template-140 div.img.rightside span';
    $response['action'] = $content->action;
    //$response['action'] = 'prepend';
    //$response['action'] = 'replace';
    //$response['content'] = $content->delta[0]['content'];
    //$response['content'] = '3.80%*';

    foreach ($content->delta as $key => $delta) {
      if ($params['triger'] == $delta['triger']) {
        $response['content'] = $delta['content'];
      }
    }

    App::renderJSONP($response);
  }

  public function getAlACarteContent() {
    $content = new Content();
    $content->name = $name;
    $content->triger = '#test_id';
    $content->save();
    App::render("<h2>Your AlACarte content, ". $name .", has been saved</h2>");
  }

  public function saveAlACarteContent($app) {
    $request = $app->request->getJsonRawBody();

    if (isset($request->doc_id)) {
      $document = Content::findById($request->doc_id); 
    }else{
      $document = new Content();
    }

    $document->action = $request->action;
    $document->target_id = $request->target_id;
    $document->site = $request->site;
    $document->path = $request->path;
    $document->label = $request->label;
    $document->delta = $request->delta;
    $document->save();


    App::renderJSON($request);
  }

  public function updateAlACarteContent($app) {
    $request = $app->request->getJsonRawBody();
    if ($request->doc_id) {
      $document = Content::findById($request->doc_id); 
    }else{
      $document = new Content();
    }

    $document->action = $request->action;
    $document->target_id = $request->target_id;
    $document->site = $request->site;
    $document->path = $request->path;
    $document->label = $request->label;
    $document->delta = $request->delta;
    $document->save();

    App::renderJSON($request);
    //App::renderJSON($request);
  }

  public function orderAlACarteContent($app) {
    $request = $app->request->getJsonRawBody();

    $response = $app->response;
    $response->setHeader('Access-Control-Allow-Origin', '*'); $response->setHeader('Access-Control-Allow-Headers', 'X-Requested-With');
    $response->sendHeaders();
    //App::renderJSON($app->request->getJsonRawBody());

    $trigers = array();

    foreach ($request->trigers as $key => $triger) {
      $trigers[] = $triger->triger;
    }
 
    $documents = Content::find(array(
      "conditions" => array(
        "site" => $request->site,
        "path" => $request->path
      )
    ));

    $responses = array();
    foreach ($documents as $key => $document) {
      $response = array();
      $response['target_id'] = $document->target_id;
      $response['action'] = $document->action;
    
      foreach ($document->delta as $key => $delta) {
        if (in_array($delta['triger'], $trigers)) {
          $response['content'] = $delta['content'];
        }
      } 

      $responses[] = $response;
    }



    App::renderJSON($responses);
    //App::render('HELLO WORLD');
  }

  public function deleteAlACarteContent($app) {
    $request = $app->request->getJsonRawBody();

    if ($request->delete == 'all') {
      $all_content = Content::find();
      foreach ($all_content as $content) {
        $content->delete();
      }
    }

    App::render('All AlACarte Data has been removed.');
  }

  public function getAlACarteDoc($doc_id) {
    $document = Content::findById($doc_id);
    //App::render('This is your doc ID: ' . $doc_id);
    App::renderJSON($document);
  }

  public function deleteAlACarteDoc($doc_id) {
    $document = Content::findById($doc_id);
    if ($document) {
      $document->delete();
      $status = 'Document Deleted';
    }else{
      $status = 'Document Not Found';
    }
    App::renderJSON(array('status' => $status));
  }

  public function listSitesAlACarteDocs($site) {
    $documents = Content::find(array(
      array("site" => $site)
      ));

    $alacarte = array();
    foreach ($documents as $key => $document) {
      $doc_id = get_object_vars($document->getId());
      $alacarte[] = array(
        'doc_id' => $doc_id['$id'],
        'path' => $document->path,
        'label' => $document->label,
        'action' => $document->action,
        'target_id' => $document->target_id,
      );
    }

    //App::render('This is your doc ID: ' . $doc_id);
    App::renderJSON($alacarte);
  }

  public function welcomeName($name) {

    App::render('Welcome: ' . $name);
  }

  public function render($content) {

    echo $content;
  }

  public function renderJSON($content) {

    echo json_encode(array($content));
  }

  public function renderJSONP($content) {

    echo 'AlACarte(' . json_encode(array($content)) . ');';
  }
  
}

# App section
$app = new App($di);

$app->get('/', "App::render_Root");
$app->get('/test', "App::test");

$app->get('/alacarte/doc/{doc_id}', "App::getAlACarteDoc");
$app->get('/alacarte/doc/{doc_id}/delete', "App::deleteAlACarteDoc");
$app->post('/alacarte/doc/update', function() use ($app){App::updateAlACarteContent($app);});
$app->post('/alacarte/doc/save', function() use ($app){App::saveAlACarteContent($app);});
$app->get('/alacarte/docs/{site}/all', "App::listSitesAlACarteDocs");

$app->post('/alacarte/order', function() use ($app){App::orderAlACarteContent($app);});

$app->get('/say/welcome/{name}', "App::welcomeName");


$app->post('/alacarte/delete', function() use ($app){App::deleteAlACarteContent($app);});

$app->get('/content/{site}/{id}/{triger}', function ($site, $id, $triger) {
  echo "<This is your content>";

});




$app->handle();