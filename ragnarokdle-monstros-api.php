<?php
include_once 'nomes_estilizados_de_todos_os_pokemons.php';
$pokeapi = new PokePHP\PokeApi;

function obter_dados($nome_estilizado_do_pokemon, $geracao) {
  global $pokeapi;
  global $nomes_estilizados_de_todos_os_pokemons;
  $id = array_search(strtolower($nome_estilizado_do_pokemon), array_map(function($n) {return strtolower($n);}, $nomes_estilizados_de_todos_os_pokemons));
  //$pokespecie_secreto = json_decode($pokeapi->pokemonSpecies($nome_estilizado_do_pokemon));
  //echo $id;exit;
  $pokespecie_secreto = json_decode($pokeapi->pokemonSpecies($id));
  //var_dump($pokespecie_secreto->id);exit;
  if (empty($pokespecie_secreto->id))
    return ['erro' => 'Pokémon não encontrado: '.$nome_estilizado_do_pokemon];
  $pokemon_secreto = json_decode($pokeapi->pokemon($pokespecie_secreto->id));
  //var_dump($pokemon_secreto);exit;
  $numero_de_pokemons_por_geracao = [
    [0,151],
    [151,100],
    [251,135],
    [386,107],
    [493,156],
    [649,72],
    [721,88],
    [809,96],
    [905,120]
  ];

  $tipos = [
  	"normal" => "Normal",
    "fighting" => "Lutador",
    "flying" => "Voador",
    "poison" => "Venenoso",
    "ground" => "Terra",
    "rock" => "Pedra",
    "bug" => "Inseto",
    "ghost" => "Fantasma",
    "steel" => "Metálico",
    "fire" => "Fogo",
    "water" => "Água",
    "grass" => "Planta",
    "electric" => "Elétrico",
    "psychic" => "Psíquico",
    "ice" => "Gelo",
    "dragon" => "Dragão",
    "dark" => "Noturno",
    "fairy" => "Fada",
    "stellar" => "Estelar",
    "unknown" => "Desconhecido",
    "nenhum" => "Nenhum"
  ];
  $cores = [
    "black" => "Preto",
    "blue" => "Azul",
    "brown" => "Marrom",
    "gray" => "Cinza",
    "green" => "Verde",
    "pink" => "Rosa",
    "purple" => "Roxo",
    "red" => "Vermelho",
    "white" => "Branco",
    "yellow" => "Amarelo"
  ];
  
  $nome = $nomes_estilizados_de_todos_os_pokemons[$pokespecie_secreto->id];
  $tipo1 = $pokemon_secreto->types[0]->type->name;
  $tipo2 = 'nenhum';
  if (!empty($pokemon_secreto->types[1]))
    $tipo2 = $pokemon_secreto->types[1]->type->name;
  if (!empty($pokemon_secreto->past_types)) {
    $url = $pokemon_secreto->past_types[0]->generation->url;
    $geracao_do_tipo_anterior = str_replace('/','', substr($url, strrpos(substr($url, 0, strlen($url)-1),'/')));
    if ($geracao_do_tipo_anterior >= $geracao) {
      $tipo1 = $pokemon_secreto->past_types[0]->types[0]->type->name;
      $tipo2 = 'nenhum';
      if (!empty($pokemon_secreto->past_types[0]->types[1]))
        $tipo2 = $pokemon_secreto->past_types[0]->types[1]->type->name;
    }
  }
  //$habitat = $pokespecie_secreto->habitat->name;
  $cor = $pokespecie_secreto->color->name;
  //$estagio_de_evolucao = $evolucoes->chain->species->name;
  $evoluido = 'Não';
  if (!empty($pokespecie_secreto->evolves_from_species)) {
    $url = $pokespecie_secreto->evolves_from_species->url;
    //$id_da_preevolucao = str_replace('/','', substr($url, strrpos(substr($url, 0, strlen($url)-1),'/')));
    $url = substr($url, 0, strlen($url)-1);
    $id_da_preevolucao = substr($url, strrpos($url,'/')+1);
    $id_do_ultimo = $numero_de_pokemons_por_geracao[$geracao-1][0] + $numero_de_pokemons_por_geracao[$geracao-1][1];
    if ($id_da_preevolucao <= $id_do_ultimo)
      $evoluido = 'Sim';
  }
  $altura = $pokemon_secreto->height;
  $peso = $pokemon_secreto->weight;
  $url_do_sprite = $pokemon_secreto->sprites->front_default;
  //var_dump($pokemon_secreto->id);exit;
  
  return (object) [
    'id'=>$pokemon_secreto->id*1,
    'nome'=>$nome,
    'tipo1'=>$tipos[$tipo1],
    'tipo2'=>$tipos[$tipo2],
    //'habitat'=>$habitat,
    'cor'=>$cores[$cor],
    //'estagio_de_evolucao'=>$estagio_de_evolucao,
    'evoluido'=>$evoluido,
    'altura'=>$altura/10,
    'peso'=>$peso/10,
    'url_do_sprite'=>$url_do_sprite
  ];
}

if ($api == 'pokedle-api') {
  if ($versao == 'v1') {
      if ($metodo == 'POST' && $acao == 'jogo') {
      $postp_geracoes = '';
      if(array_key_exists('geracoes', $post_params))
        $postp_geracoes = $post_params['geracoes'];

      $geracao_contexto;
      if(isset($post_params['geracao_contexto']))
        $geracao_contexto = $post_params['geracao_contexto'];

      if (empty($postp_geracoes)) {
        http_response_code(400);
        echo json_encode(['erro' => 'É preciso informar pelo menos uma geração.']);
        exit;
      }
      $geracoes;
      if (is_array($postp_geracoes))
        $geracoes = $postp_geracoes;
      else
        $geracoes = explode(',', $postp_geracoes);
      foreach ($geracoes as $g) {
        if (!is_numeric($g)) {
          http_response_code(400);
          echo json_encode(['erro' => 'As gerações devem conter apenas números inteiros separados por vírgula.']);
          exit;
        }
        $g = $g * 1;
        if (!is_int($g)) {
          http_response_code(400);
          echo json_encode(['erro' => 'As gerações devem conter apenas números inteiros separados por vírgula.']);
          exit;
        }
        if ($g > 9 || $g < 1) {
          http_response_code(400);
          echo json_encode(['erro' => 'As gerações devem ser números entre 1 e 9']);
          exit;
        }
      }
      $geracoes = array_map(function ($i) {return $i*1;}, $geracoes);
      sort($geracoes);
      $geracao = max($geracoes);

      if (empty($geracao_contexto))
        $geracao_contexto = $geracao;
      else {
        if (!is_numeric($geracao_contexto)) {
          http_response_code(400);
          echo json_encode(['erro' => 'A geração do contexto deve conter apenas um número inteiro.']);
          exit;
        }
        $geracao_contexto = $geracao_contexto * 1;
        if (!is_int($geracao_contexto)) {
          http_response_code(400);
          echo json_encode(['erro' => 'A geração do contexto deve conter apenas um número inteiro.']);
          exit;
        }
        if ($geracao_contexto > 9 || $geracao_contexto < 1) {
          http_response_code(400);
          echo json_encode(['erro' => 'A geração do contexto deve ser um número entre 1 e 9']);
          exit;
        }
        if ($geracao > $geracao_contexto) {
          http_response_code(400);
          echo json_encode(['erro' => 'A geração do contexto não pode ser menor que a maior geração escolhida.']);
          exit;
        }
      }

      $geracao = $geracao_contexto*1;
      $numero_de_pokemons_por_geracao = [
        [0,151],
        [151,100],
        [251,135],
        [386,107],
        [493,156],
        [649,72],
        [721,88],
        [809,96],
        [905,120]
      ];
      $G_offset = 0;
      $G_limit = 0;
      $pks = [];
      $pksps = [];
      $pokemons_da_geracao = [];

      foreach ($geracoes as $g) {
        $G_offset = $numero_de_pokemons_por_geracao[$g-1][0];
        $G_limit = $numero_de_pokemons_por_geracao[$g-1][1];
        $G_url = 'https://pokeapi.co/api/v2/pokemon-species/?offset='.$G_offset.'&limit='.$G_limit;
        $pks = json_decode($pokeapi->sendRequest($G_url))->results;
        $pokemons_da_geracao = array_merge($pokemons_da_geracao, $pks);
      }

      $nomes_dos_pokemons_das_geracoes = [];
      $nomes_url_dos_pokemons_das_geracoes = [];
      $nomes_estilizados_dos_pokemons_das_geracoes = [];
      $ids_dos_pokemons_das_geracoes = [];
      $urls_dos_sprites = [];
      foreach ($pokemons_da_geracao as $pg) {
        $nomes_dos_pokemons_das_geracoes[] = $pg->name;
        $nomes_url_dos_pokemons_das_geracoes[] = $pg->name;
        $id = (int) str_replace('/','', substr($pg->url, strrpos(substr($pg->url, 0, strlen($pg->url)-1),'/')));
        $ids_dos_pokemons_das_geracoes[] = $id;
        //$nomes_dos_pokemons_das_geracoes[] = $nomes_de_todos_os_pokemons[array_search($pg->name, $nomes_de_todos_os_pokemons)];
        $nomes_estilizados_dos_pokemons_das_geracoes[] = $nomes_estilizados_de_todos_os_pokemons[$id];
        $urls_dos_sprites[] = 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/'.$id.'.png';
      }

      $seed = (int) date("Ymd");
      srand($seed);
      $total_de_pokemons_das_geracoes = count($pokemons_da_geracao);
      $indice_do_pokemon_secreto = (rand() % $total_de_pokemons_das_geracoes);
      $id_do_pokemon_secreto = $ids_dos_pokemons_das_geracoes[$indice_do_pokemon_secreto];

      $pkscrt = obter_dados($nomes_estilizados_de_todos_os_pokemons[$id_do_pokemon_secreto], $geracao);
      //$uuid = uuid_create(UUID_TYPE_TIME);
      //$_SESSION['id'] = $uuid;
      $_SESSION['seed'] = $seed;
      $_SESSION['geracoes'] = $geracoes;
      $_SESSION['geracao_contexto'] = $geracao_contexto;
      $_SESSION['total_de_pokemons_das_geracoes_selecionadas'] = $total_de_pokemons_das_geracoes;
      $_SESSION['ids_dos_pokemons_das_geracoes_selecionadas'] = $ids_dos_pokemons_das_geracoes;
      //$_SESSION['nomes_dos_pokemons_das_geracoes_selecionadas'] = $nomes_dos_pokemons_das_geracoes;
      $_SESSION['nomes_url_dos_pokemons_das_geracoes_selecionadas'] = $nomes_url_dos_pokemons_das_geracoes;
      $_SESSION['nomes_estilizados_dos_pokemons_das_geracoes_selecionadas'] = $nomes_estilizados_dos_pokemons_das_geracoes;
      $_SESSION['nomes_dos_pokemons_das_geracoes_selecionadas'] = $nomes_estilizados_dos_pokemons_das_geracoes;
      $_SESSION['urls_dos_sprites_dos_pokemons_das_geracoes_selecionadas'] = $urls_dos_sprites;
      $_SESSION['pokemon_secreto'] = $pkscrt;
      $_SESSION['descobriu'] = false;
      $_SESSION['palpites'] = [];

      echo json_encode([
        'seed' => $seed,
        'jogo' => 'pokedle',
        'geracoes' => $geracoes,
        'geracao_contexto' => $geracao_contexto
      ]);
      exit;
    }

    if ($metodo == 'GET' && $acao == 'jogo') {
      if (empty($_SESSION['geracoes'])) {
        http_response_code(403);
        echo json_encode(['erro' => 'Não há jogos em andamento em sua sessão.']);
        exit;
      }
      $jogo = [
        'seed' => $_SESSION['seed'],
        'geracoes' => $_SESSION['geracoes'],
        'geracao_contexto' => $_SESSION['geracao_contexto'],
        'total_de_pokemons_das_geracoes_selecionadas' => $_SESSION['total_de_pokemons_das_geracoes_selecionadas'],
        'total_de_palpites' => count($_SESSION['palpites']),
        'descobriu' => $_SESSION['descobriu']
      ];
      echo json_encode($jogo);
      exit;
    }
    
    if ($metodo == 'GET' && $acao == 'pokemons') {
      if (empty($_SESSION['geracoes'])) {
        http_response_code(403);
        echo json_encode(['erro' => 'Inicie uma sessão para poder jogar.']);
        exit;
      }
      echo json_encode([
        "ids_dos_pokemons_das_geracoes_selecionadas" => $_SESSION['ids_dos_pokemons_das_geracoes_selecionadas'],
        //"nomes_dos_pokemons_das_geracoes_selecionadas" => $_SESSION['nomes_dos_pokemons_das_geracoes_selecionadas'],
        "nomes_url_dos_pokemons_das_geracoes_selecionadas" => $_SESSION['nomes_url_dos_pokemons_das_geracoes_selecionadas'],
        "nomes_estilizados_dos_pokemons_das_geracoes_selecionadas" => $_SESSION['nomes_estilizados_dos_pokemons_das_geracoes_selecionadas'],
        "nomes_dos_pokemons_das_geracoes_selecionadas" => $_SESSION['nomes_estilizados_dos_pokemons_das_geracoes_selecionadas'],
        "urls_dos_sprites_dos_pokemons_das_geracoes_selecionadas" => $_SESSION['urls_dos_sprites_dos_pokemons_das_geracoes_selecionadas']
      ]);
      exit;
    }

    if ($metodo == 'GET' && $acao == 'palpites') {
      if (empty($_SESSION['geracoes'])) {
        http_response_code(403);
        echo json_encode(['erro' => 'Inicie uma sessão para poder jogar.']);
        exit;
      }
      echo json_encode(['palpites' => $_SESSION['palpites']]);
      exit;
    }

    if ($metodo == 'POST' && $acao == 'palpites') {
      if (empty($_SESSION['geracoes'])) {
        http_response_code(403);
        echo json_encode(['erro' => 'Inicie uma sessão para poder jogar.']);
        exit;
      }
      if (empty($post_params['palpite'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Digite o nome do pokémon.']);
        exit;
      }
      $pk = $post_params['palpite'];
      $pokemon = obter_dados($pk, $_SESSION['geracao_contexto']);
      if (empty($pokemon->id)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Pokémon não encontrado']);
        exit;
      }
      //if (array_search($pokemon->nome, $_SESSION['nomes_dos_pokemons_das_geracoes_selecionadas']) === false) {
      //if (array_search($pokemon->nome, $_SESSION['nomes_estilizados_dos_pokemons_das_geracoes_selecionadas']) === false) {
      if (array_search(strtolower($pokemon->nome), array_map(function($n) {return strtolower($n);}, $_SESSION['nomes_estilizados_dos_pokemons_das_geracoes_selecionadas'])) === false) {
        http_response_code(422);
        echo json_encode(['erro' => 'São válidos apenas pokémons das gerações selecionadas. Gerações='.implode(',', $_SESSION['geracoes'])]);
        exit;
      }
      foreach ($_SESSION['palpites'] as $p)
        if ($pokemon->nome == $p['nome']) {
          http_response_code(409);
          echo json_encode(['erro' => 'Este pokémon já foi palpitado.']);
          exit;
        }
        
      //var_dump($_SESSION['pokemon_secreto']);exit;
      //var_dump($pokemon);exit;
      $pkscrt = (object) $_SESSION['pokemon_secreto'];
      //var_dump($pkscrt);exit;
      $resultado = 
      [
        'id'=>$pokemon->id,
        'id_r'=>$pokemon->id === $pkscrt->id ? 1 : 0,
        'nome'=>$pokemon->nome,
        'nome_r'=>$pokemon->nome === $pkscrt->nome ? 1 : 0,
        'tipo1'=>$pokemon->tipo1,
        'tipo1_r'=>($pokemon->tipo1 === $pkscrt->tipo1 ? 1 : ($pokemon->tipo1 === $pkscrt->tipo2 ? 2 : 0)),
        'tipo2'=>$pokemon->tipo2,
        'tipo2_r'=>$pokemon->tipo2 === $pkscrt->tipo2 ? 1 : ($pokemon->tipo2 === $pkscrt->tipo1 ? 2 : 0),
        //'habitat'=>$pokemon->habitat,
        //'habitat_r'=>$pokemon->habitat === $pkscrt->habitat ? 1 : 0,
        'cor'=>$pokemon->cor,
        'cor_r'=>$pokemon->cor === $pkscrt->cor ? 1 : 0,
        'evoluido'=>$pokemon->evoluido,
        'evoluido_r'=>$pokemon->evoluido === $pkscrt->evoluido ? 1 : 0,
        'altura'=>$pokemon->altura,
        'altura_r'=>$pokemon->altura === $pkscrt->altura ? 1 : ($pokemon->altura > $pkscrt->altura ? 2 : 0),
        'peso'=>$pokemon->peso,
        'peso_r'=>$pokemon->peso === $pkscrt->peso ? 1 : ($pokemon->peso > $pkscrt->peso ? 2 : 0),
        'url_do_sprite'=>$pokemon->url_do_sprite
      ];
      $_SESSION['palpites'][] = $resultado;
      if ($pokemon->id == $pkscrt->id)
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