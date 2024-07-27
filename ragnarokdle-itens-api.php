<?php
include_once 'nomes_estilizados_de_todas_as_tecnicas.php';
$pokeapi = new PokePHP\PokeApi;

function obter_dados($nome_estilizado_da_tecnica, $geracao) {
  global $pokeapi;
  global $nomes_estilizados_de_todas_as_tecnicas;
  $id = array_search(strtolower($nome_estilizado_da_tecnica), array_map(function($n) {return strtolower($n);}, $nomes_estilizados_de_todas_as_tecnicas));
  //$tecnica_secreta = json_decode($pokeapi->move($nome_estilizado_da_tecnica));
  //echo $id;exit;
  $tecnica_secreta = json_decode($pokeapi->move($id));
  //var_dump($tecnica_secreta->id);exit;
  if (empty($tecnica_secreta->id))
    return ['erro' => 'Técnica não encontrada: '.$nome_estilizado_da_tecnica];
  $tecnica_secreta = json_decode($pokeapi->move($tecnica_secreta->id));
  $numero_de_tecnicas_por_geracao = [
    [0,165],
    [165,86],
    [251,103],
    [354,113],
    [467,92],
    [559,62],
    [621,121],
    [742,108],
    [850,69]
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
    "unknown" => "???",
    "nenhum" => "Nenhum"
  ];
  $categorias = [
    "physical" => "Física",
    "special" => "Especial",
    "status" => "Status"
  ];
  $geracao_dos_jogos = [
    "red-blue" => 1,
    "yellow" => 1,
    "gold-silver" => 2,
    "crystal" => 2,
    "ruby-sapphire" => 3,
    "emerald" => 3,
    "firered-leafgreen" => 3,
    "diamond-pearl" => 4,
    "platinum" => 4,
    "heartgold-soulsilver" => 4,
    "black-white" => 5,
    "colosseum" => 3,
    "xd" => 3,
    "black-2-white-2" => 5,
    "x-y" => 6,
    "omega-ruby-alpha-sapphire" => 6,
    "sun-moon" => 7,
    "ultra-sun-ultra-moon" => 7,
    "lets-go-pikachu-lets-go-eevee" => 7,
    "sword-shield" => 8,
    "the-isle-of-armor" => 8,
    "the-crown-tundra" => 8,
    "brilliant-diamond-and-shining-pearl" => 8,
    "legends-arceus" => 8,
    "scarlet-violet" => 9,
    "the-teal-mask" => 9,
    "the-indigo-disk" => 9 
  ];

  $nome = $nomes_estilizados_de_todas_as_tecnicas[$tecnica_secreta->id];
  $tipo = $tipos[$tecnica_secreta->type->name];
  $poder = $tecnica_secreta->power;
  if ($poder === null)
    $poder = '--';
  $precisao = $tecnica_secreta->accuracy;
  if ($precisao === null)
    $precisao = '--';
  $pp = $tecnica_secreta->pp;
  $categoria = $categorias[$tecnica_secreta->damage_class->name];
  $ailment = $tecnica_secreta->meta->ailment->name;
  $causa_ailment = $ailment == 'paralysis' || $ailment == 'sleep' || $ailment == 'freeze' || $ailment == 'burn' || $ailment == 'poison' ? 'Sim' : 'Não';
  $afeta_stat = $tecnica_secreta->stat_changes ? 'Sim' : 'Não';
  $cura_usuario = $tecnica_secreta->meta->drain > 0 || $tecnica_secreta->meta->healing > 0 ? 'Sim' : 'Não';
  //$estagio_de_evolucao = $evolucoes->chain->species->name;
  $url_da_categoria = $tecnica_secreta->meta->category->url;
  $id_da_categoria = str_replace('/','', substr($url_da_categoria, strrpos(substr($url_da_categoria, 0, strlen($url_da_categoria)-1),'/')));
  //$cura_usuario = $id_da_categoria == 3 || $id_da_categoria == 8 ? 'Sim' : 'Não';
  $efeito_unico = $id_da_categoria >= 9 && $id_da_categoria <= 12 || $tecnica_secreta->name == 'tar-shot' ? 'Sim' : 'Não';
  //if (!empty($tecnica_secreta->evolves_from_species)) {
  //  $url = $tecnica_secreta->evolves_from_species->url;
  //  //$id_da_preevolucao = str_replace('/','', substr($url, strrpos(substr($url, 0, strlen($url)-1),'/')));
  //  $url = substr($url, 0, strlen($url)-1);
  //  $id_da_preevolucao = substr($url, strrpos($url,'/')+1);
  //  $id_do_ultimo = $numero_de_tecnicas_por_geracao[$geracao-1][0] + $numero_de_tecnicas_por_geracao[$geracao-1][1];
  //  if ($id_da_preevolucao <= $id_do_ultimo)
  //    $evoluido = 'Sim';
  //}
  $quantos_podem_aprender = 0;
  if ($tecnica_secreta->learned_by_pokemon)
    $quantos_podem_aprender = count($tecnica_secreta->learned_by_pokemon);
  
  if (!empty($tecnica_secreta->past_values)) {
    for ($i=0; $i < count($tecnica_secreta->past_values); $i++) { 
      $pv = $tecnica_secreta->past_values[$i];
      $geracao_pv = $geracao_dos_jogos[$pv->version_group->name];
      if ($geracao < $geracao_pv) {
        if ($pv->type)
          $tipo = $tipos[$pv->type->name];
        if ($pv->power !== null)
          $poder = $pv->power;
        if ($pv->accuracy !== null)
          $precisao = $pv->accuracy;
        if ($pv->pp !== null)
          $pp = $pv->pp;
        break;
      }
    }
  }

  //correções na pokeapi (de acordo com a bulbapedia)
  if ($tecnica_secreta->name == 'bide' && ($geracao == 2 || $geracao == 3))
    $precisao = 100;
  if (($tecnica_secreta->name == 'mimic' || $tecnica_secreta->name == 'pain-split') && $geracao <= 2)
    $precisao = 100;
  if (($tecnica_secreta->name == 'fore-sight' || $tecnica_secreta->name == 'lock-on' || $tecnica_secreta->name == 'mind-reader'
    || $tecnica_secreta->name == 'odor-sleuth' || $tecnica_secreta->name == 'struggle') && $geracao <= 3)
    $precisao = 100;
  if (($tecnica_secreta->name == 'memento' || $tecnica_secreta->name == 'nightmare') && $geracao <= 3)
    $precisao = '--';
  if (($tecnica_secreta->name == 'roar' || $tecnica_secreta->name == 'whirlwind') && $geracao <= 5)
    $precisao = 100;
  if ($tecnica_secreta->name == 'topsy-turvy' && $geracao <= 6)
    $precisao = 100;

  if ($geracao <= 3 && ($tecnica_secreta->name == 'weather-ball' || $tecnica_secreta->name == 'hidden-power'))
    $categoria = 'Varia';
  
  if ($tecnica_secreta->name == 'volt-tackle' && $geracao <= 3)
    $causa_ailment = 'Não';
  if ($tecnica_secreta->name == 'tri-attack' && $geracao >= 2)
    $causa_ailment = 'Sim';

  if ($tecnica_secreta->name == 'rapid-spin' && $geracao <= 7)
    $afeta_stat = 'Não';
  
  //$url_da_geracao = $tecnica_secreta->generation->url;
  //$id_da_preevolucao = str_replace('/','', substr($url_da_geracao, strrpos(substr($url_da_geracao, 0, strlen($url_da_geracao)-1),'/')));
  //$url_da_geracao = substr($url_da_geracao, 0, strlen($url_da_geracao)-1);
  //$id_da_preevolucao = substr($url_da_geracao, strrpos($url_da_geracao,'/')+1);
  //$geracao_da_tecnica = $tecnica_secreta->sprites->front_default;
  //var_dump($tecnica_secreta->id);exit;
  
  return (object) [
    'id'=>$tecnica_secreta->id*1,
    'nome'=>$nome,
    'tipo'=>$tipo,
    'poder'=>$poder,
    'precisao'=>$precisao,
    'pp'=>$pp,
    'categoria'=>$categoria,
    'afeta_stat'=>$afeta_stat,
    'causa_ailment'=>$causa_ailment,
    'cura_usuario'=>$cura_usuario,
    'efeito_unico'=>$efeito_unico,
    'quantos_podem_aprender'=>$quantos_podem_aprender
  ];
}

if ($api == 'pokedle-moves-api') {
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
      $numero_de_tecnicas_por_geracao = [
        [0,165],
        [165,86],
        [251,103],
        [354,113],
        [467,92],
        [559,62],
        [621,121],
        [742,108],
        [850,69]
      ];
      $G_offset = 0;
      $G_limit = 0;
      $tecs = [];
      $tecnicas_das_geracoes = [];

      foreach ($geracoes as $g) {
        $G_offset = $numero_de_tecnicas_por_geracao[$g-1][0];
        $G_limit = $numero_de_tecnicas_por_geracao[$g-1][1];
        $G_url = 'https://pokeapi.co/api/v2/move/?offset='.$G_offset.'&limit='.$G_limit;
        $tecs = json_decode($pokeapi->sendRequest($G_url))->results;
        $tecnicas_das_geracoes = array_merge($tecnicas_das_geracoes, $tecs);
      }

      $nomes_url_das_tecnicas_das_geracoes = [];
      $nomes_estilizados_das_tecnicas_das_geracoes = [];
      $ids_das_tecnicas_das_geracoes = [];
      foreach ($tecnicas_das_geracoes as $tg) {
        $nomes_url_das_tecnicas_das_geracoes[] = $tg->name;
        $id = (int) str_replace('/','', substr($tg->url, strrpos(substr($tg->url, 0, strlen($tg->url)-1),'/')));
        $ids_das_tecnicas_das_geracoes[] = $id;
        $nomes_estilizados_das_tecnicas_das_geracoes[] = $nomes_estilizados_de_todas_as_tecnicas[$id];
      }

      $seed = (int) date("Ymd");
      srand($seed);
      $total_de_tecnicas_das_geracoes = count($tecnicas_das_geracoes);
      $indice_da_tecnica_secreta = (rand() % $total_de_tecnicas_das_geracoes);
      $id_da_tecnica_secreta = $ids_das_tecnicas_das_geracoes[$indice_da_tecnica_secreta];

      $tecscrt = obter_dados($nomes_estilizados_de_todas_as_tecnicas[$id_da_tecnica_secreta], $geracao);
      //$uuid = uuid_create(UUID_TYPE_TIME);
      //$_SESSION['id'] = $uuid;
      $_SESSION['seed'] = $seed;
      $_SESSION['geracoes'] = $geracoes;
      $_SESSION['geracao_contexto'] = $geracao_contexto;
      $_SESSION['total_de_tecnicas_das_geracoes_selecionadas'] = $total_de_tecnicas_das_geracoes;
      $_SESSION['ids_das_tecnicas_das_geracoes_selecionadas'] = $ids_das_tecnicas_das_geracoes;
      $_SESSION['nomes_url_das_tecnicas_das_geracoes_selecionadas'] = $nomes_url_das_tecnicas_das_geracoes;
      $_SESSION['nomes_das_tecnicas_das_geracoes_selecionadas'] = $nomes_estilizados_das_tecnicas_das_geracoes;
      $_SESSION['tecnica_secreta'] = $tecscrt;
      $_SESSION['descobriu'] = false;
      $_SESSION['palpites'] = [];

      echo json_encode([
        'seed' => $seed,
        'jogo' => 'pokedle-moves',
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
        'total_de_tecnicas_das_geracoes_selecionadas' => $_SESSION['total_de_tecnicas_das_geracoes_selecionadas'],
        'total_de_palpites' => count($_SESSION['palpites']),
        'descobriu' => $_SESSION['descobriu']
      ];
      echo json_encode($jogo);
      exit;
    }
    
    if ($metodo == 'GET' && $acao == 'tecnicas') {
      if (empty($_SESSION['geracoes'])) {
        http_response_code(403);
        echo json_encode(['erro' => 'Inicie uma sessão para poder jogar.']);
        exit;
      }
      echo json_encode([
        "ids_das_tecnicas_das_geracoes_selecionadas" => $_SESSION['ids_das_tecnicas_das_geracoes_selecionadas'],
        "nomes_url_das_tecnicas_das_geracoes_selecionadas" => $_SESSION['nomes_url_das_tecnicas_das_geracoes_selecionadas'],
        "nomes_das_tecnicas_das_geracoes_selecionadas" => $_SESSION['nomes_das_tecnicas_das_geracoes_selecionadas'],
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
        echo json_encode(['erro' => 'Digite o nome da técnica.']);
        exit;
      }
      $tec = $post_params['palpite'];
      $tecnica = obter_dados($tec, $_SESSION['geracao_contexto']);
      if (empty($tecnica->id)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Técnica não encontrada']);
        exit;
      }
      if (array_search(strtolower($tecnica->nome), array_map(function($n) {return strtolower($n);}, $_SESSION['nomes_das_tecnicas_das_geracoes_selecionadas'])) === false) {
        http_response_code(422);
        echo json_encode(['erro' => 'São válidas apenas técnicas das gerações selecionadas. Gerações='.implode(',', $_SESSION['geracoes'])]);
        exit;
      }
      foreach ($_SESSION['palpites'] as $p)
        if ($tecnica->nome == $p['nome']) {
          http_response_code(409);
          echo json_encode(['erro' => 'Esta técnica já foi palpitada.']);
          exit;
        }

      $tecscrt = (object) $_SESSION['tecnica_secreta'];
      //var_dump($tecscrt);exit;
      $resultado = 
      [
        'id'=>$tecnica->id,
        'id_r'=>$tecnica->id === $tecscrt->id ? 1 : 0,
        'nome'=>$tecnica->nome,
        'nome_r'=>$tecnica->nome === $tecscrt->nome ? 1 : 0,
        'tipo'=>$tecnica->tipo,
        'tipo_r'=>$tecnica->tipo === $tecscrt->tipo ? 1 : 0,
        'poder'=>$tecnica->poder,
        'poder_r'=>$tecnica->poder === $tecscrt->poder ? 1 : ($tecnica->poder == '--' || $tecscrt->poder == '--' ? -1 : ($tecnica->poder > $tecscrt->poder ? 2 : 0)),
        'precisao'=>$tecnica->precisao,
        'precisao_r'=>$tecnica->precisao === $tecscrt->precisao ? 1 : ($tecnica->precisao == '--' || $tecscrt->precisao == '--' ? -1 : ($tecnica->precisao > $tecscrt->precisao ? 2 : 0)),
        'pp'=>$tecnica->pp,
        'pp_r'=>$tecnica->pp === $tecscrt->pp ? 1 : ($tecnica->pp > $tecscrt->pp ? 2 : 0),
        'categoria'=>$tecnica->categoria,
        'categoria_r'=>$tecnica->categoria === $tecscrt->categoria ? 1 : 0,
        'afeta_stat'=>$tecnica->afeta_stat,
        'afeta_stat_r'=>$tecnica->afeta_stat === $tecscrt->afeta_stat ? 1 : 0,
        'causa_ailment'=>$tecnica->causa_ailment,
        'causa_ailment_r'=>$tecnica->causa_ailment === $tecscrt->causa_ailment ? 1 : 0,
        'cura_usuario'=>$tecnica->cura_usuario,
        'cura_usuario_r'=>$tecnica->cura_usuario === $tecscrt->cura_usuario ? 1 : 0,
        'efeito_unico'=>$tecnica->efeito_unico,
        'efeito_unico_r'=>$tecnica->efeito_unico === $tecscrt->efeito_unico ? 1 : 0,
        'quantos_podem_aprender'=>$tecnica->quantos_podem_aprender,
        'quantos_podem_aprender_r'=>$tecnica->quantos_podem_aprender === $tecscrt->quantos_podem_aprender ? 1
          : ($tecnica->quantos_podem_aprender > $tecscrt->quantos_podem_aprender ? 2 : 0)
      ];
      $_SESSION['palpites'][] = $resultado;
      if ($tecnica->id == $tecscrt->id)
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