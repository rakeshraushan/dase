The Grant Proposal Application Module (itsprop) is required to do operations on the underlying DASe collection that the logged in user is not authorized to do.  If the operations are done strictly within the handler (or other PHP code), we can simply embed the serviceuser and password in the code.  But when the browser needs to do such operations, we need a (relatively) secure way for the browser to access the serviceuser password.  The step are shown here.

  * Create a serviceuser by setting a config variable in the DASe local config file (note: cannot use module's config, since some requests initiated by module will not go through module).

({dase}/inc/local\_config.php):

```
$conf['serviceuser']['itsprop'] = 'ok'  //'ok' can be anything that evaluates to true
```


  * in the login method, set a secret in a cookie, using serviceuser as key:

```
public function getLogin($r)
  {
    $user = Uteid::login($r);
    $secret = Dase_Auth::getSecret('itsprop');
    Dase_Cookie::set('module',$secret);
    ...
  }
```


  * On the layout template, include a link w/ rel=service\_pass and href that will point to a resource in the module which will return the service password and will require a valid logged-in user to do so

```
<link rel="service_pass" href="{$module_root}service_pass/itsprop" />
```

and here is the mapper & method:

```
'service_pass/{serviceuser}' => 'service_pass',
```
```
public function getServicePass($r)
{   
    $secret = Dase_Cookie::get('module');
    $suser = $r->get('serviceuser');
    //checks the secret that was saved in cookie upon login
    if ($secret == Dase_Auth::getSecret($r->get('serviceuser'))) {
       //note: serviceuser MUST be declared in MODULE_ROOT.'/inc/config.php'
       $r->renderResponse(Dase_Auth::getServicePassword($suser));
     } else {
        $r->renderError(401);
     }
}
```

  * And some javascript to get the service password (note that this will ONLY work if the secret cookie is set):

```
Dase.getServicePassword = function() {
    var url = Dase.getLinkByRel('service_pass');
    Dase.ajax(url,'get',function(resp) {
        if (32 == resp.length) {
            Dase.itsprop.service_pass = resp;
            //any code that needs the service password must
            //be initialized here
        }
    });
}
```

  * thus we guarantee that only a valid user currently logged into THIS module will e able to access the service password