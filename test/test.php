<?

  include 'SabreAMF.php';

  $request = new SabreAMFRequest();
  $request->execute();

  if ($credentials = $request->getCredentials()) {
    
     echo('Credentials: '  . $credentials['username'] . ':' . $credentials['password'] . "\n");

  }

  $response = new SabreAMFResponse();

  foreach($request->getCalls() as $call) {

        $response->enableSessions(true);
        $response->addResult($call['target']);

  }


?>
