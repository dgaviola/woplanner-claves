<!DOCTYPE html>
<html>
<head>
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet">
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
<div class="row">
  <h1>Generador de claves para woplanner</h1>
  <?
  if (isset($_POST['codigoPc']) && isset($_POST['usuario'])) {
    $claveCalculadaInfinita = clavesCalcularClave($_POST['codigoPc'], $_POST['usuario'], null, true);
    $clave = '';

    for ($i = 0; $i < 12; $i++) {
      if ($i == 0)
        $clave .= '' . $claveCalculadaInfinita[$i];
      else
        $clave .= '-' . $claveCalculadaInfinita[$i];
    }
  ?>
  <div class="panel">
    <div class="alert alert-success">
    La clave es: <strong><? echo $clave ?></strong>
    </div>
    <a href="/">Volver</a>
  </div>
  <?
  } else {
  ?>
  <form class="form-horizontal" method="POST">
  <div class="form-group">
    <label for="codigoPc" class="col-sm-2 control-label">Codigo PC</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="codigoPc" id="codigoPc" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="usuario" class="col-sm-2 control-label">Usuario</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="usuario" id="usuario" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-default">Calcular!</button>
    </div>
  </div>
  </form>
  <?
  }
  ?>
</div>
</div>
</body>
</html>

<?

/*
// datos de ejemplo
$codigoPC = "UZGDWQMFPK";
$usuario = "leandro";
*/

function cortar8bits($num) {
  $num = $num & 255;

  return $num;
}

function cortar4Bajos($num) {
  $num = $num & 15;

  return $num;
}

function cortar4Altos($num) {
  $num = $num & 240;

  return $num;
}

function claveSumar($c1, $a1, $c2, $a2, $c3, $a3, $altos)
{
  cortar8bits($c1);
  cortar8bits($c2);
  cortar8bits($c3);
    $res = 0;

    if($a1) {
        $c1 = $c1 >> 4;
        $c1 = cortar4Bajos($c1);
    }
    else
    {
      $c1 = cortar4Bajos($c1);
    }

    if($a2) {
        $c2 = $c2 >> 4;
        $c2 = cortar4Bajos($c2);
    }
    else
    {
      $c2 = cortar4Bajos($c2);
    }

    if($a3) {
        $c3 = $c3 >> 4;
        $c3 = cortar4Bajos($c3);
    }
    else
    {
      $c3 = cortar4Bajos($c3);
    }

    $res = $c1 + $c2;
    $res = cortar4Bajos($res);
    $res = $res + $c3;
    $res = cortar4Bajos($res);

    if($altos)
        $res = $res << 4;

    return $res;
}

function clavesCombinar($c1, $c2)
{
  cortar8bits($c1);
  cortar8bits($c2);
    $r = 0;

  $c1 = cortar4Altos($c1);
    $c1 = $c1 >> 4;
    $c1 = $c1 << 4;
    $c2 = cortar4Bajos($c2);
    $r = $c1 | $c2;

    return $r;
}

function clavesValorBit($c1, $c2, $f, $nro)
{
  cortar8bits($c1);
  cortar8bits($c2);
  cortar8bits($f);
  cortar8bits($nro);

  if(!($c1&$nro) && !($c2&$nro) && !($f&$nro)) return false;
    if(!($c1&$nro) && !($c2&$nro) && ($f&$nro)) return true;
    if(!($c1&$nro) && ($c2&$nro) && !($f&$nro)) return false;
    if(!($c1&$nro) && ($c2&$nro) && ($f&$nro)) return true;
    if(($c1&$nro) && !($c2&$nro) && !($f&$nro)) return false;
    if(($c1&$nro) && !($c2&$nro) && ($f&$nro)) return true;
    if(($c1&$nro) && ($c2&$nro) && !($f&$nro)) return false;
    if(($c1&$nro) && ($c2&$nro) && ($f&$nro)) return true;

    return true;
}

function calvesDescifrar($c1a, $c1b, $c2a, $c2b, $res)
{
  cortar8bits($c1a);
  cortar8bits($c1b);
  cortar8bits($c2a);
  cortar8bits($c2b);
  cortar8bits($res);

  $cod = 0;

  for($cod=0; $cod<255; $cod++)
    {
        $t = claveSumar($c1a, true, $c2a, true, $cod, true, true) |
            claveSumar($c1b, false, $c2b, false, $cod, false, false);
        $t = $t%26;
        $t += 97;

        if($t==$res) {
            break;
        }
    }

    return $cod;
}

function clavesInsertarFecha($Fecha, $CLAVE)
{
  $CLAVEC = array();

    $ano = date('Y', strtotime($Fecha));
    $mes = date('m', strtotime($Fecha));
    $dia = date('d', strtotime($Fecha));
    $ano -= 2000;

    for($i=0; $i<12; $i++)
        $CLAVEC[i] = 0;

    $pos_bit = 0;

    if($mes&1)
        $pos_bit |= 1;
    if($CLAVE[0]&128)
        $pos_bit |= 2;

    switch($pos_bit)
    {
    case 0: $comp = 1; break;
    case 1: $comp = 2; break;
    case 2: $comp = 4; break;
    case 3: $comp = 8; break;
    }

    $nro_bit = 7; $nro_char = 0;

    for($i=0; $i<12; $i++)
    {
        for($j=7; $j>=0; $j--)
        {
          $saltar = false;

            switch($i)
            {
            case 0:
                if($j==7) { $CLAVEC[0] |= ($mes&1)?128:0; $saltar = true;continue; }
                else if($j==$pos_bit) { $CLAVEC[0] |= ($mes&2)?$comp:0; $saltar = true;continue; }
                break;
            case 1:
                if($j==$pos_bit) { $CLAVEC[1] |= ($mes&4)?$comp:0; $saltar = true;continue; }
                break;
            case 2:
                if($j==$pos_bit) { $CLAVEC[2] |= ($mes&8)?$comp:0; $saltar = true;continue; }
                break;
            case 3:
                if($j==$pos_bit) { $CLAVEC[3] |= ($dia&1)?$comp:0; $saltar = true;continue; }
                break;
            case 4:
                if($j==$pos_bit) { $CLAVEC[4] |= ($dia&2)?$comp:0; $saltar = true;continue; }
                break;
            case 5:
                if($j==$pos_bit) { $CLAVEC[5] |= ($dia&4)?$comp:0; $saltar = true;continue; }
                break;
            case 6:
                if($j==$pos_bit) { $CLAVEC[6] |= ($dia&8)?$comp:0; $saltar = true;continue; }
                break;
            case 7:
                if($j==$pos_bit) { $CLAVEC[7] |= ($dia&16)?$comp:0; $saltar = true;continue; }
                break;
            case 8:
                if($j==$pos_bit) { $CLAVEC[8] |= ($ano&1)?$comp:0; $saltar = true;continue; }
                else if($j==$pos_bit+1) { $CLAVEC[8] |= ($ano&2)?($comp*2):0; $saltar = true;continue; }
                break;
            case 9:
                if($j==$pos_bit) { $CLAVEC[9] |= ($ano&4)?$comp:0; $saltar = true;continue; }
                else if($j==$pos_bit+1) { $CLAVEC[9] |= ($ano&8)?($comp*2):0; $saltar = true;continue; }
                break;
            case 10:
                if($j==$pos_bit) { $CLAVEC[10] |= ($ano&16)?$comp:0; $saltar = true;continue; }
                break;
            case 11:
                if($j==$pos_bit) { $CLAVEC[11] |= ($ano&32)?$comp:0; $saltar = true;continue; }
                else if($j==$pos_bit+1) { $CLAVEC[11] |= ($ano&64)?($comp*2):0; $saltar = true;continue; }
                break;
            }

            if ($saltar) continue;

            if($CLAVE[$nro_char]&(int)pow(2, $nro_bit))
                $CLAVEC[$i] |= (int)pow(2, $j);

            $nro_bit--;

            if($nro_bit<0)
            {
                $nro_char++;
                $nro_bit = 7;
            }
        }
    }

    return $CLAVEC;
}

function clavesInsertarFechaInfinita($CLAVE)
{
    $CLAVEC = array();

  for($i=0; $i<12; $i++)
        $CLAVEC[$i] = 0;

    $pos_bit = 0;

    if($CLAVE[0]&128)
        $pos_bit |= 2;

    $nro_bit = 7; $nro_char = 0;

    for($i=0; $i<12; $i++)
    {
        for($j=7; $j>=0; $j--)
        {
          $saltar = false;

          switch($i)
            {
            case 0:
                if($j==7) { $saltar = true; continue; }
                else if($j==$pos_bit) { $saltar = true;continue; }
                break;
            case 1:
                if($j==$pos_bit) { $saltar = true;continue; }
                break;
            case 2:
                if($j==$pos_bit) { $saltar = true;continue; }
                break;
            case 3:
                if($j==$pos_bit) { $saltar = true;continue; }
                break;
            case 4:
                if($j==$pos_bit) { $saltar = true;continue; }
                break;
            case 5:
                if($j==$pos_bit) { $saltar = true;continue; }
                break;
            case 6:
                if($j==$pos_bit) { $saltar = true;continue; }
                break;
            case 7:
                if($j==$pos_bit) { $saltar = true;continue; }
                break;
            case 8:
                if($j==$pos_bit) { $saltar = true;continue; }
                else if($j==$pos_bit+1) { $saltar = true;continue; }
                break;
            case 9:
                if($j==$pos_bit) { $saltar = true;continue; }
                else if($j==$pos_bit+1) { $saltar = true;continue; }
                break;
            case 10:
                if($j==$pos_bit) { $saltar = true;continue; }
                break;
            case 11:
                if($j==$pos_bit) { $saltar = true;continue; }
                else if($j==$pos_bit+1) { $saltar = true;continue; }
                break;
            }
      if ($saltar) continue;

            if((int)$CLAVE[$nro_char]&(int)pow(2, $nro_bit)) {
                $CLAVEC[$i] |= (int)pow(2, $j);
            }

            $nro_bit--;

            if($nro_bit<0)
            {
                $nro_char++;
                $nro_bit = 7;
            }
        }
    }

    return $CLAVEC;
}

function clavesCalcularClave($NroPC, $Usuario, $Fecha, $Infinita)
{
  //var_dump($NroPC, $Usuario, $Fecha, $Infinita);
  $CLAVE = array();
    $u = array(); $p = array(); $c = array(); $f = array(); $r = array();

    $f = "woplannera";

    for($i=0; $i<strlen($Usuario) && $i<10; $i++)
        $u[$i] = ord($Usuario[$i]);

    while($i<10)
    {
        for($j=0; $j<strlen($Usuario) && $i<10; $j++, $i++)
            $u[$i] = ord($Usuario[$j]);
    }

    for($i=0; $i<strlen($NroPC); $i++)
        $p[$i] = ord($NroPC[$i]);

    $c[0] = calvesDescifrar($u[0], $u[0], $p[3], $p[9], ord($f[0]));
    $c[1] = calvesDescifrar(cortar4Altos($u[6]<<4), $u[3], $p[1], $p[1], ord($f[1]));
    $c[2] = calvesDescifrar($u[4], $u[8], cortar4Altos($p[8]<<4), cortar4Bajos($p[0]>>4), ord($f[2]));
    $c[3] = calvesDescifrar($u[1], cortar4Bajos($u[3]>>4), $p[8], $p[3], ord($f[3]));
    $c[4] = calvesDescifrar(cortar4Altos($u[5]<<4), $u[2], cortar4Altos($p[0]<<4), cortar4Bajos($p[5]>>4), ord($f[4]));
    $c[5] = calvesDescifrar(cortar4Altos($u[4]<<4), $u[1], $p[6], $p[2], ord($f[5]));
    $c[6] = calvesDescifrar(cortar4Altos($u[7]<<4), $u[9], $p[9], $p[4], ord($f[6]));
    $c[7] = calvesDescifrar($u[5], cortar4Bajos($u[2]>>4), $p[2], $p[7], ord($f[7]));
    $c[8] = calvesDescifrar($u[8], cortar4Bajos($u[6]>>4), $p[4], cortar4Bajos($p[7]>>4), ord($f[8]));
    $c[9] = calvesDescifrar($u[7], cortar4Bajos($u[9]>>4), cortar4Altos($p[5]<<4), $p[6], ord($f[9]));
    $c[10] = '\0';

    $r[0] = clavesCombinar(cortar4Altos($c[0]<<4), $c[7]);
    $r[1] = clavesCombinar(cortar4Altos($c[5]<<4), cortar4Bajos($c[3]>>4));
    $r[2] = clavesCombinar(cortar4Altos($c[9]<<4), $c[2]);
    $r[3] = clavesCombinar($c[0], $c[1]);
    $r[4] = clavesCombinar($c[6], cortar4Bajos($c[4]>>4));
    $r[5] = clavesCombinar($c[9], $c[3]);
    $r[6] = clavesCombinar($c[1], $c[8]);
    $r[7] = clavesCombinar($c[8], $c[4]);
    $r[8] = clavesCombinar($c[7], cortar4Bajos($c[5]>>4));
    $r[9] = clavesCombinar(cortar4Altos($c[6]<<4), cortar4Bajos($c[2]>>4));

    for($i=0; $i<10; $i++)
        $CLAVE[$i] = $r[$i];

    if($Infinita)
        $CLAVEC = clavesInsertarFechaInfinita($CLAVE);
    else
        $CLAVEC = clavesInsertarFecha($Fecha, $CLAVE);


    return $CLAVEC;
}

?>
