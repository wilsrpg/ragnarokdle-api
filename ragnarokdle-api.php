<?php
include_once 'nomes_estilizados_de_todos_os_monstros.php';
include_once 'nomes_estilizados_de_todas_as_armas.php';

function obter_dados($nome_estilizado) {
  global $nomes_estilizados_de_todos_os_monstros;
  //$nomes_minusculos = array_map(function($n) {return strtolower($n['nome']);}, $nomes_estilizados_de_todos_os_monstros);
  $indice = array_search(strtolower($nome_estilizado), array_map(function($n) {return strtolower($n['nome']);}, $nomes_estilizados_de_todos_os_monstros));
  if ($indice === false)
    return ['erro' => 'Palpite não encontrado: '.$nome_estilizado];

  $id = $nomes_estilizados_de_todos_os_monstros[$indice]['id'];
  //echo 'aki '.$nome_estilizado;exit;
  //echo $indice;exit;
  //echo $id;exit;

  $ch = curl_init();
  $URL_BASE = 'https://www.divine-pride.net/api/database/';
  $CATEGORIA = '/Monster/';
  $API_KEY = '?apiKey=7e9552d32c9990d74dd961c53f1a6eed';
  $IDIOMA = '&server=bRO';

  curl_setopt($ch, CURLOPT_URL, $URL_BASE.$CATEGORIA.$id.$API_KEY.$IDIOMA);
  //curl_setopt($ch, CURLOPT_URL, $URL_BASE.$id);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
  //curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);  //tell cUrl where to write cookie data
  //curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile); //tell cUrl where to read cookie data from
  //curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID='.$_COOKIE['PHPSESSID']);

  $dados = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($http_code != 200) {
    return json_encode(['erro' => 'Erro na comunicação com o servidor: '.curl_error($ch)]);
  }

  if (empty($dados))
    return ['erro' => 'Palpite não encontrado: '.$nome_estilizado];
  //var_dump($dados);exit;
  $secreto = json_decode($dados);
  //var_dump($secreto);exit;

  $URL_BASE_PRO_NIVEL = 'https://ragnapi.com/api/v1/old-times/monsters/';
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $URL_BASE_PRO_NIVEL.$id);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
  $dados = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($http_code != 200) {
    return json_encode(['erro' => 'Erro na comunicação com o servidor: '.curl_error($ch)]);
  }
  if (empty($dados))
    return ['erro' => 'Palpite não encontrado: '.$nome_estilizado];
  //var_dump($dados);exit;
  $nivel = json_decode($dados)->main_stats->level*1;

  $racas = [
  	"demi_human" => "Humanoide",
    "brute" => "Bruto",
    "plant" => "Planta",
    "insect" => "Inseto",
    "fish" => "Peixe",
    "formless" => "Amorfo",
    "undead" => "Morto-vivo",
    "dragon" => "Dragão",
    "angel" => "Anjo",
    "demon" => "Demônio"
  ];
  $tamanhos = [
  	"small" => "Pequeno",
    "medium" => "Médio",
    "large" => "Grande"
  ];
  $propriedades = [
    "neutral" => "Neutro",
    "fire" => "Fogo",
    "earth" => "Terra",
    "wind" => "Vento",
    "water" => "Água",
    "poison" => "Veneno",
    "undead" => "Maldito",
    "ghost" => "Fantasma",
    "holy" => "Sagrado",
    "shadow" => "Sombrio"
  ];

  //na ordem da API do divine-pride:
  $racas2 = [
    'Amorfo',
    'Morto-Vivo',
    'Bruto',
    'Planta',
    'Inseto',
    'Peixe',
    'Demônio',
    'Humanoide',
    'Anjo',
    'Dragão',
  ];
  $tamanhos2 = [
    'Pequeno',
    'Médio',
    'Grande',
  ];
  $propriedades2 = [
    'Neutro',
    'Água',
    'Terra',
    'Fogo',
    'Vento',
    'Veneno',
    'Sagrado',
    'Sombrio',
    'Fantasma',
    'Maldito'
  ];

  $nome = $nomes_estilizados_de_todos_os_monstros[$indice]['nome'];
  //$nivel = $secreto->main_stats->level*1;
  //$raca = $racas[$secreto->race];
  //$tamanho = $tamanhos[$secreto->size];
  //$propriedade = $propriedades[$secreto->type];
  //$nivel_prop = $secreto->element_power;
  //$nivel = $secreto->stats->level;
  $raca = $racas2[$secreto->stats->race];
  $tamanho = $tamanhos2[$secreto->stats->scale];
  $propriedade = $propriedades2[$secreto->stats->element%20];
  $nivel_prop = floor($secreto->stats->element/20);
  $mapas = $secreto->spawn;
  $drops = $secreto->drops;
  $maior_drop = ['id' => 0, 'chance' => 0, 'nome' => ''];
  foreach ($drops as $drop) {
    if ($drop->chance > $maior_drop['chance']) {
      $maior_drop['id'] = $drop->itemId;
      $maior_drop['chance'] = $drop->chance;
    }
  }

  $CATEGORIA = '/Item/';
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $URL_BASE.$CATEGORIA.$maior_drop['id'].$API_KEY.$IDIOMA);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

  $dados = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($http_code != 200) {
    return json_encode(['erro' => 'Erro na comunicação com o servidor: '.curl_error($ch)]);
  }
  if (empty($dados))
    return ['erro' => 'Palpite não encontrado: '.$nome_estilizado];
  //var_dump($dados);exit;
  $maior_drop['nome'] = json_decode($dados)->name;
  //var_dump($maior_drop);exit;

  $mvp = $secreto->stats->mvp == 1;
  $miniboss = $secreto->stats->class == 1 && $secreto->stats->mvp == 0;
  $escravos = $secreto->slaves;
  //var_dump($secreto->id);exit;

  return (object) [
    'id'=>$id,
    'nome'=>$nome,
    'nivel'=>$nivel,
    'raca'=>$raca,
    'tamanho'=>$tamanho,
    'propriedade'=>$propriedade,
    'nivel_prop'=>$nivel_prop,
    'mapas'=>$mapas,
    'drops'=>$drops,
    'maior_drop'=>$maior_drop,
    'mvp'=>$mvp,
    'miniboss'=>$miniboss,
    'escravos'=>$escravos
  ];
}

function obter_dados_da_arma($nome_estilizado) {
  global $nomes_estilizados_de_todas_as_armas;
  //$nomes_minusculos = array_map(function($n) {return strtolower($n['nome']);}, $nomes_estilizados_de_todas_as_armas);
  //echo $nome_estilizado;exit;
  $indice = array_search(strtolower($nome_estilizado), array_map(function($n) {return strtolower($n['nome']);}, $nomes_estilizados_de_todas_as_armas));
  //echo $indice;exit;
  if ($indice === false)
    return ['erro' => 'Palpite não encontrado: '.$nome_estilizado];
  $id = $nomes_estilizados_de_todas_as_armas[$indice]['id'];
  //echo $id;exit;

  $ch = curl_init();
  $URL_BASE = 'https://www.divine-pride.net/api/database/';
  $CATEGORIA = '/Item/';
  //$URL_BASE = 'https://ragnapi.com/api/v1/old-times/monsters/';
  $API_KEY = '?apiKey=7e9552d32c9990d74dd961c53f1a6eed';
  $IDIOMA = '&server=bRO';

  curl_setopt($ch, CURLOPT_URL, $URL_BASE.$CATEGORIA.$id.$API_KEY.$IDIOMA);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
  //curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);  //tell cUrl where to write cookie data
  //curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile); //tell cUrl where to read cookie data from
  //curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID='.$_COOKIE['PHPSESSID']);

  $dados = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($http_code != 200) {
    return json_encode(['erro' => 'Erro na comunicação com o servidor: '.curl_error($ch)]);
  }

  if (empty($dados))
    return ['erro' => 'Arma não encontrada: '.$nome_estilizado];
  //var_dump($dados);exit;
  $arma_secreta = json_decode($dados);
  //var_dump($arma_secreta);exit;



  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $URL_BASE.$CATEGORIA.$id.$API_KEY);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

  $dados = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($http_code != 200) {
    return json_encode(['erro' => 'Erro na comunicação com o servidor: '.curl_error($ch)]);
  }

  if (empty($dados))
    return ['erro' => 'Arma não encontrada: '.$nome_estilizado];
  //var_dump($dados);exit;
  $nivel_da_arma = json_decode($dados)->itemLevel;
  //var_dump($arma_secreta);exit;



  
  $URL_BASE_PRO_DROP = 'https://ragnapi.com/api/v1/old-times/items/';
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $URL_BASE_PRO_DROP.$id);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
  $dados = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($http_code != 200) {
    return json_encode(['erro' => 'Erro na comunicação com o servidor: '.curl_error($ch)]);
  }
  //var_dump($dados);exit;
  if (empty($dados))
    return ['erro' => 'Palpite não encontrado: '.$nome_estilizado];
  $dropado_por = json_decode($dados)->drop_rate;
//var_dump($dropado_por);exit;


  //na ordem da API do divine-pride:
  $tipos = [
    256 => "Adaga",
    257 => "Espada",
    258 => "Espada de duas mãos",
    259 => "Lança",
    260 => "Lança de duas mãos",
    261 => "Machado",
    262 => "Machado de duas mãos",
    263 => "Maça",
    264 => "[q tipo eh esse?]",
    265 => "Cajado",
    266 => "Cajado de duas mãos",
    267 => "Arco",
    268 => "Soqueira",
    269 => "Instrumento musical",
    270 => "Chicote",
    271 => "Livro",
    272 => "Katar",
    273 => "Pistola",
    274 => "Rifle",
    275 => "Metralhadora",
    276 => "Espingarda",
    277 => "Lança-granadas",
    278 => "Shuriken huuma"
  ];
  $propriedades = [
    'Neutro',
    'Água',
    'Terra',
    'Fogo',
    'Vento',
    'Veneno',
    'Sagrado',
    'Sombrio',
    'Fantasma',
    'Maldito'
  ];
  $palavras_chave = [
    "Adaga",
    "Alaúde",
    "Arco",
    "Bastão",
    "Besta",
    "Botas",
    "Cajado",
    "Chicote",
    "Clava",
    "de duas mãos",
    "de uma mão",
    "Diário",
    "Espada",
    "Espingarda",
    "Faca",
    "Foice",
    "Garra",
    "Guitarra",
    "Huuma",
    "Kataná",
    "Katar",
    "Lâmina",
    "Lança",
    "Lança-granadas",
    "Livro",
    "Maça",
    "Machado",
    "Mangual",
    "Martelo",
    "Metralhadora",
    "Pistola",
    "Punhal",
    "Punho",
    "Rifle",
    "Sabre",
    "Shuriken",
    "Soqueira",
    "Violão"
  ];

  $nome = $nomes_estilizados_de_todas_as_armas[$indice]['nome'];
  $tipo = $tipos[$arma_secreta->itemSubTypeId];
  //$nivel_da_arma = $arma_secreta->itemLevel; //quando server=bRO, vem como null
  $slots = $arma_secreta->slots;
  $ataque = $arma_secreta->attack;
  $propriedade = $propriedades[$arma_secreta->attribute];
  if ($arma_secreta->id == 1144) //sashimi tá errada na API
    $propriedade = $propriedades[4];
  $peso = $arma_secreta->weight;
  $preco_de_venda = $arma_secreta->price/2;
  $pode_ser_comprado = count($arma_secreta->soldBy) > 0;
  $descricao = preg_replace('/[\^][\dabcdef]{6}/', '', $arma_secreta->description);
  $descricao = preg_replace('/----+/', '', $descricao);
  $descricao_com_mascara = str_ireplace($nome, '[*]', $descricao);
  foreach ($palavras_chave as $palavra) {
    $descricao_com_mascara = str_ireplace($palavra, '[*]', $descricao_com_mascara);
  }
//var_dump($dropado_por);exit;

  return (object) [
    'id'=>$id,
    'nome'=>$nome,
    'slots'=>$slots,
    'tipo'=>$tipo,
    'nivel_da_arma'=>$nivel_da_arma,
    'ataque'=>$ataque,
    'propriedade'=>$propriedade,
    'peso'=>$peso,
    'preco_de_venda'=>$preco_de_venda,
    'pode_ser_comprado'=>$pode_ser_comprado,
    'descricao'=>$descricao,
    'descricao_com_mascara'=>$descricao_com_mascara,
    'dropado_por'=>$dropado_por
  ];
}

if ($api == 'ragnarokdle-api') {
  if ($versao == 'v1') {
      if ($metodo == 'POST' && $acao == 'jogo') {
      $modo = '';
      if(array_key_exists('modo', $post_params))
        $modo = $post_params['modo'];

      if (empty($modo)) {
        http_response_code(400);
        echo json_encode(['erro' => 'É preciso informar o modo de jogo.']);
        exit;
      }

      $seed = (int) date("Ymd");
      //srand($seed);
      $array = [];
      if ($modo == 'monstro')
        $array = $nomes_estilizados_de_todos_os_monstros;
      if ($modo == 'arma')
        $array = $nomes_estilizados_de_todas_as_armas;

      $nomes = array_map(function($n) {return $n['nome'];}, $array);
      $ids = array_map(function($n) {return $n['id'];}, $array);

      $total_de_nomes = count($array);
      $indice_do_secreto = (rand() % $total_de_nomes);
      //$id_do_secreto = $ids[$indice_do_secreto];

      if ($modo == 'monstro')
        $secreto = obter_dados($array[$indice_do_secreto]['nome']);
      if ($modo == 'arma')
        $secreto = obter_dados_da_arma($array[$indice_do_secreto]['nome']);
      
      $dicas = [];
      if ($modo == 'monstro')
        $dicas = [$secreto->mapas, $secreto->maior_drop, $secreto->mvp, $secreto->miniboss, $secreto->escravos];
      if ($modo == 'arma')
        $dicas = [$secreto->descricao, $secreto->dropado_por, $secreto->descricao_com_mascara];

      $_SESSION['seed'] = $seed;
      $_SESSION['modo'] = $modo;
      $_SESSION['total_de_nomes'] = $total_de_nomes;
      $_SESSION['ids'] = $ids;
      $_SESSION['nomes'] = $nomes;
      $_SESSION['secreto'] = $secreto;
      $_SESSION['descobriu'] = false;
      $_SESSION['palpites'] = [];

      echo json_encode([
        'seed' => $seed,
        'modo' => $_SESSION['modo'],
        'dicas' => $dicas
      ]);
      exit;
    }

    if ($metodo == 'GET' && $acao == 'jogo') {
      if (empty($_SESSION['modo'])) {
        http_response_code(403);
        echo json_encode(['erro' => 'Não há jogos em andamento em sua sessão.']);
        exit;
      }
      $jogo = [
        'seed' => $_SESSION['seed'],
        'modo' => $_SESSION['modo'],
        //'total_de_nomes' => $_SESSION['total_de_nomes'],
        'total_de_palpites' => count($_SESSION['palpites']),
        'descobriu' => $_SESSION['descobriu']
      ];
      echo json_encode($jogo);
      exit;
    }
    
    if ($metodo == 'GET' && $acao == 'nomes') {
      if (empty($_SESSION['modo'])) {
        http_response_code(403);
        echo json_encode(['erro' => 'Inicie uma sessão para poder jogar.']);
        exit;
      }
      echo json_encode([
        "ids" => $_SESSION['ids'],
        "nomes" => $_SESSION['nomes']
      ]);
      exit;
    }

    if ($metodo == 'GET' && $acao == 'palpites') {
      if (empty($_SESSION['modo'])) {
        http_response_code(403);
        echo json_encode(['erro' => 'Inicie uma sessão para poder jogar.']);
        exit;
      }
      echo json_encode(['palpites' => $_SESSION['palpites']]);
      exit;
    }

    if ($metodo == 'POST' && $acao == 'palpites') {
      if (empty($_SESSION['modo'])) {
        http_response_code(403);
        echo json_encode(['erro' => 'Inicie uma sessão para poder jogar.']);
        exit;
      }
      if (empty($post_params['palpite'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Digite um palpite.']);
        exit;
      }
      
      $modo = $_SESSION['modo'];
      $array = [];
      if ($modo == 'monstro')
        $array = $nomes_estilizados_de_todos_os_monstros;
      if ($modo == 'arma')
        $array = $nomes_estilizados_de_todas_as_armas;

      $indice = array_search(strtolower($post_params['palpite']), array_map(function($n) {return strtolower($n['nome']);}, $array));
      $palpite;
      if ($modo == 'monstro')
        $palpite = obter_dados($array[$indice]['nome']);
      if ($modo == 'arma')
        $palpite = obter_dados_da_arma($array[$indice]['nome']);
      //echo json_encode(['erro'=>$palpite->nome]);exit;

      if (empty($palpite->id)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Palpite não encontrado.']);
        exit;
      }
      foreach ($_SESSION['palpites'] as $p)
        if ($palpite->nome == $p['nome']) {
          http_response_code(409);
          echo json_encode(['erro' => 'Palpite repetido.']);
          exit;
        }

      //var_dump($_SESSION['secreto']);exit;
      //var_dump($palpite);exit;
      //echo json_encode(['erro'=>$palpite->nome]);exit;
      $secreto = (object) $_SESSION['secreto'];
      //var_dump($secreto);exit;
      $resultado = [];
      if ($modo == 'monstro')
        $resultado = [
          'id'=>$palpite->id,
          'id_r'=>$palpite->id === $secreto->id ? 1 : 0,
          'nome'=>$palpite->nome,
          'nome_r'=>$palpite->nome === $secreto->nome ? 1 : 0,
          'nivel'=>$palpite->nivel,
          'nivel_r'=>$palpite->nivel === $secreto->nivel ? 1 : ($palpite->nivel > $secreto->nivel ? 2 : 0),
          'raca'=>$palpite->raca,
          'raca_r'=>$palpite->raca === $secreto->raca ? 1 : 0,
          'tamanho'=>$palpite->tamanho,
          'tamanho_r'=>$palpite->tamanho === $secreto->tamanho ? 1 : 0,
          'propriedade'=>$palpite->propriedade,
          'propriedade_r'=>$palpite->propriedade === $secreto->propriedade ? 1 : 0,
          'nivel_prop'=>$palpite->nivel_prop,
          'nivel_prop_r'=>$palpite->nivel_prop === $secreto->nivel_prop ? 1
            : ($palpite->nivel_prop > $secreto->nivel_prop ? 2 : 0)
        ];
        //var_dump($secreto);exit;
        //echo json_encode(['erro'=>$palpite->tipo]);exit;
      if ($modo == 'arma')
        $resultado = [
          'id'=>$palpite->id,
          'id_r'=>$palpite->id === $secreto->id ? 1 : 0,
          'nome'=>$palpite->nome,
          'nome_r'=>$palpite->nome === $secreto->nome ? 1 : 0,
          'tipo'=>$palpite->tipo,
          'tipo_r'=>$palpite->tipo === $secreto->tipo ? 1 : 0,
          'slots'=>$palpite->slots,
          'slots_r'=>$palpite->slots === $secreto->slots ? 1 : 0,
          'nivel_da_arma'=>$palpite->nivel_da_arma,
          'nivel_da_arma_r'=>$palpite->nivel_da_arma === $secreto->nivel_da_arma ? 1 : 0,
          'ataque'=>$palpite->ataque,
          'ataque_r'=>$palpite->ataque === $secreto->ataque ? 1 : ($palpite->ataque > $secreto->ataque ? 2 : 0),
          'propriedade'=>$palpite->propriedade,
          'propriedade_r'=>$palpite->propriedade === $secreto->propriedade ? 1 : 0,
          'peso'=>$palpite->peso,
          'peso_r'=>$palpite->peso === $secreto->peso ? 1 : ($palpite->peso > $secreto->peso ? 2 : 0),
          'preco_de_venda'=>$palpite->preco_de_venda,
          'preco_de_venda_r'=>$palpite->preco_de_venda === $secreto->preco_de_venda ? 1
            : ($palpite->preco_de_venda > $secreto->preco_de_venda ? 2 : 0),
          'pode_ser_comprado'=>$palpite->pode_ser_comprado,
          'pode_ser_comprado_r'=>$palpite->pode_ser_comprado === $secreto->pode_ser_comprado ? 1 : 0
        ];

      //echo json_encode(['erro'=>$palpite->nivel_da_arma]);exit;
      $_SESSION['palpites'][] = $resultado;
      if ($palpite->id == $secreto->id)
        $_SESSION['descobriu'] = true;

      //echo json_encode(['resultado' => $resultado]);
      echo json_encode($resultado);
      exit;
    }

    http_response_code(404);
    echo json_encode(['erro' => 'Rota não encontrada: "' . $acao.'"']);
    exit;
  }

  http_response_code(404);
  echo json_encode(['erro' => 'Versão não encontrada: "'.$versao.'"']);
  exit;
}

http_response_code(404);
echo json_encode(['erro' => 'API não encontrada: "'.$api.'"']);
exit;