<?php
  require_once 'vendor/autoload.php';

  $app = new \Slim\Slim();

  $db = new mysqli('localhost', 'root','','curso_angular4');

  //Configuración de Cabeceras HTTP en PHP para permitir el acceso CORS
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Allow: GET, POST, OPTIONS, PUT, DELETE");
  $method = $_SERVER['REQUEST_METHOD'];
  if($method == "OPTIONS") {
      die();
  }


  $app ->get("/pruebas", function() use($app, $db){

    echo "Hola mundo des de Slim";
  });

//MÈTODE PER LLISTAR ELS PRODUCTES
$app->get('/productos', function() use($db, $app){
  $sql='SELECT * FROM productos ORDER BY id DESC;';
  $query=$db->query($sql);

  //generem un array d'objectes
  $productos = array();
  while ($producto = $query->fetch_assoc()){
    $productos[]=$producto;
  }

  $result=array(
    'status'=>'success',
    'code'=>200,
    'data'=> $productos
  );

  echo json_encode($result);

//  var_dump($query->fetch_all());
});


//MÈTODE PER RETORNAR 1 SOL PRODUCTES
$app->get('/producto/:id', function($id) use($db, $app){
  $sql='SELECT * FROM productos WHERE id='.$id;
  $query=$db->query($sql);

  $result = array(
    "status"=>'error',
    "code"=>404,
    'message'=>'Producto no disponible'
  );

  if($query->num_rows ==1){
    $producto = $query->fetch_assoc();

    $result = array(
      "status"=>'success',
      "code"=>200,
      'data'=>$producto
    );
  }
  echo json_encode($result);
});

//MÈTODE PER ELIMINAR 1 PRODUCTES
$app->get('/delete-producto/:id', function($id) use($db,$app){
  $sql = 'DELETE FROM productos WHERE id='.$id;
  $query = $db-> query($sql);

  if ($query){
    $result = array(
      "status"    =>'success',
      "code"      =>200,
      'message'   => 'El producto se ha eliminado correctamente'
    );
  }else{
    $result = array(
      "status"    =>'error',
      "code"      =>404,
      'message'   => 'El producto NO se ha eliminado'
    );
  }
  echo json_encode($result);
});


//MÈTODE PER ACTUALITZAR UN PRODUCTE

$app->post('/update-producto/:id', function($id) use($db, $app){
	$json = $app->request->post('json');
	$data = json_decode($json, true);

	$sql = "UPDATE productos SET ".
		   "nombre = '{$data["nombre"]}', ".
		   "precio = '{$data["precio"]}', ";

	if(isset($data['imagen'])){
 		$sql .= "imagen = '{$data["imagen"]}', ";
	}

	$sql .=	"precio = '{$data["precio"]}' WHERE id = {$id}";


	$query = $db->query($sql);

	if($query){
		$result = array(
			'status' 	=> 'success',
			'code'		=> 200,
			'message' 	=> 'El producto se ha actualizado correctamente!!'
		);
	}else{
		$result = array(
			'status' 	=> 'error',
			'code'		=> 404,
			'message' 	=> 'El producto no se ha actualizado!!'
		);
	}

	echo json_encode($result);

});



//MÈTODE PER PUJAR UNA IMATGE A UN PRODUCTE
$app->post('/upload-file', function() use($db, $app){
	$result = array(
		'status' 	=> 'error',
		'code'		=> 404,
		'message' 	=> 'El archivo no ha podido subirse'
	);

	if(isset($_FILES['uploads'])){
		$piramideUploader = new PiramideUploader();

		$upload = $piramideUploader->upload('image', "uploads", "uploads", array('image/jpeg', 'image/png', 'image/gif'));
		$file = $piramideUploader->getInfoFile(); //aconseguim dades del fitxer que pujem
		$file_name = $file['complete_name'];      //veiem les dades del nom del fitxer

		if(isset($upload) && $upload["uploaded"] == false){
			$result = array(
				'status' 	=> 'error',
				'code'		=> 404,
				'message' 	=> 'El archivo no ha podido subirse'
			);
		}else{
			$result = array(
				'status' 	=> 'success',
				'code'		=> 200,
				'message' 	=> 'El archivo se ha subido',
				'filename'  => $file_name
			);
		}
	}

	echo json_encode($result);
});


//MÈTODE PER GUARDAR PRODUCTES
  $app->post('/productos', function() use($app, $db){
      $json=$app->request->post('json');
      $data =json_decode($json,true);

      if (!isset($data['descripcion'])){
          $data['descripcion']=null;
      }

      if (!isset($data['nombre'])){
          $data['nombre']=null;
      }

      if (!isset($data['precio'])){
          $data['precio']=null;
      }

      if (!isset($data['imagen'])){
          $data['imagen']=null;
      }


      $query ="INSERT INTO productos VALUES (NULL,".
              "'{$data['nombre']}',".
              "'{$data['descripcion']}',".
              "'{$data['precio']}',".
              "'{$data['imagen']}'".
              ")";

    //    var_dump($query);

    $insert = $db->query($query);

        $result=array(
          'status'=>'error',
          'code'=>404,
          'message'=>'Producto NO se ha creado correctamente'
        );

        if ($insert){
          $result=array(
            'status'=>'success',
            'code'=>200,
            'message'=>'Producto creado correctamente'
          );
        }

        echo json_encode($result);
  });

  $app->run();
