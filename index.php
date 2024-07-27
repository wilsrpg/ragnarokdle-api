<?php
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
date_default_timezone_set('America/Sao_Paulo');
if(session_status() === 1) {
  session_set_cookie_params(["SameSite" => "Strict"]);
  session_start();
}

require 'vendor/autoload.php';

$metodo = $_SERVER['REQUEST_METHOD'];
if ($metodo != 'GET' && $metodo != 'POST') {
  http_response_code(405);
  echo json_encode(['erro' => 'Método não suportado.']);
  exit;
}
if ($metodo == 'POST' && empty($_POST) && empty(file_get_contents('php://input'))) {
  http_response_code(405);
  echo json_encode(['erro' => 'Nenhum dado recebido.']);
  exit;
}

$caminho = '';
if(empty($_GET['caminho'])) {
  http_response_code(404);
  echo json_encode(['erro' => 'Rota não encontrada. Verifique se o endereço corresponde ao padrão domínio/api/versão/rota.']);
  exit;
}

$caminho = $_GET['caminho'];
$subdir = explode('/', $caminho);
$post_json = json_decode(file_get_contents('php://input'), true);
$post_params;
if (empty($_POST))
  $post_params = $post_json;
else
  $post_params = $_POST;

if (isset($subdir[0]))
  $api = $subdir[0];
if (isset($subdir[1]))
  $versao = $subdir[1];
if (isset($subdir[2]))
  $acao = $subdir[2];

if (empty($api) || empty($versao) || empty($acao)) {
  http_response_code(404);
  echo json_encode(['erro' => 'Rota não encontrada. Verifique se o endereço corresponde ao padrão domínio/api/versão/rota.']);
  exit;
}

if ($api == 'ragnarokdle-monstros-api')
  include_once 'ragnarokdle-monstros-api.php';
if ($api == 'ragnarokdle-itens-api')
  include_once 'ragnarokdle-itens-api.php';