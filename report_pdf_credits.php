<?php
    require_once 'func/db.php';
    // Dompdf php 7
    require_once 'dompdf_php7.1/autoload.inc.php';
    use Dompdf\Dompdf;

    // Dompdf php 5
    //require_once("dompdf_php5.6/dompdf_config.inc.php");

    session_start();
    
    $client = $_GET["client"];
    $sucursal = $_GET["sucursal"];
    $nombre_cliente = "CLIENTE: TODOS LOS CLIENTES";

    if ($client > 0)
    {
        $nombre_cliente = Return_NombreClient($client);
        if ($sucursal > 0)
        {
            $data = mysqli_query(db_conectar(),"SELECT c.id, cc.nombre, c.f_registro, INTERVAL c.dias_credit DAY + c.f_registro as f_vencimiento, c.factura, c.adeudo, c.abono, (c.adeudo - c.abono) as pd_pago, c.dias_credit, s.nombre FROM credits c, clients cc, sucursales s WHERE c.client = cc.id and c.sucursal = s.id  and c.client =  '$client' and c.sucursal = '$sucursal' ORDER by f_vencimiento asc");
        }else
        {
            $data = mysqli_query(db_conectar(),"SELECT c.id, cc.nombre, c.f_registro, INTERVAL c.dias_credit DAY + c.f_registro as f_vencimiento, c.factura, c.adeudo, c.abono, (c.adeudo - c.abono) as pd_pago, c.dias_credit, s.nombre FROM credits c, clients cc, sucursales s WHERE c.client = cc.id and c.sucursal = s.id  and c.client =  '$client' ORDER by pd_pago desc");
        }
    }else{
        if ($sucursal > 0)
        {
            $data = mysqli_query(db_conectar(),"SELECT c.id, cc.nombre, c.f_registro, INTERVAL c.dias_credit DAY + c.f_registro as f_vencimiento, c.factura, c.adeudo, c.abono, (c.adeudo - c.abono) as pd_pago, c.dias_credit, s.nombre FROM credits c, clients cc, sucursales s WHERE c.client = cc.id and c.sucursal = s.id and c.pay = 0 and c.sucursal = '$sucursal' ORDER by f_vencimiento asc");
        }else
        {
            $data = mysqli_query(db_conectar(),"SELECT c.id, cc.nombre, c.f_registro, INTERVAL c.dias_credit DAY + c.f_registro as f_vencimiento, c.factura, c.adeudo, c.abono, (c.adeudo - c.abono) as pd_pago, c.dias_credit, s.nombre FROM credits c, clients cc, sucursales s WHERE c.client = cc.id and c.sucursal = s.id and c.pay = 0 ORDER by f_vencimiento asc");
        }
    }

    
    $body = '';
    while($row = mysqli_fetch_array($data))
    {
            $body = $body.'
            <tr>
            <td class="item-des">'.$row[1].'</td>
            <td class="item-des">'.$row[9].'</td>
            <td class="item-des">'.$row[4].'</td>
            <td class="item-des"><center>'.$row[2].'</center></td>
            <td class="item-des"><center>'.$row[3].'</center></td>
            <td class="item-des"><center>'.$row[8].' DIAS</center></td>
            <td class="item-des">$ '.number_format($row[5],GetNumberDecimales(),".",",").' MXN</td>
            <td class="item-des">$ '.number_format($row[6],GetNumberDecimales(),".",",").' MXN</td>
            <td class="item-des">$ '.number_format($row[7],GetNumberDecimales(),".",",").' MXN</td>
            </tr>
            ';
            $total = $total + $row[7];
    }
    
    $codigoHTML='
    <h1><center>'.$_SESSION['empresa_nombre'].'</center></h1>
    <h3><center>'.$_SESSION['empresa_direccion'].'</center></h3>
    <h3><center>MAIL: '.$_SESSION['empresa_correo'].' | TEL: '.$_SESSION['empresa_telefono'].'</center></h3>
    <h4><center>HISTORIA DE CREDITOS: '.$nombre_cliente.'</center></h4>
    <hr>
    <br><br>
    <table style="width:100%">
        <tr>
        <th class="table-head th-name uppercase">CLIENTE</th>
        <th class="table-head th-name uppercase">SUCURSAL</th>
        <th class="table-head th-name uppercase">FACTURA</th>
        <th class="table-head th-name uppercase">F.REGISTRO</th>
        <th class="table-head th-name uppercase">F. VENCIMIENTO</th>
        <th class="table-head th-name uppercase">DIAS DE CREDITO</th>
        <th class="table-head th-name uppercase">TOTAL</th>
        <th class="table-head th-name uppercase">ABONO</th>
        <th class="table-head th-name uppercase">P. PAGO</th>
        </tr>
        '.$body.'
    </table>
    
    <br>
    <div align="right">';
    
    $codigoHTML .= '<h3>TOTAL ADEUDO: $ '.number_format($total,GetNumberDecimales(),".",",").' MXN</h3>
    </div>
    <br>
    <footer>
      <center><p>CLTA DESARROLLO & DISTRIBUCION DE SOFTWARE<br><a href="https://www.cyberchoapas.com"> www.cyberchoapas.com</a></p></center>
    </footer>
    ';
    
    $codigoHTML = mb_convert_encoding($codigoHTML, 'HTML-ENTITIES', 'UTF-8');
    $dompdf=new DOMPDF();
    $dompdf->set_paper('legal', 'landscape');
    $dompdf->load_html($codigoHTML);
    ini_set("memory_limit","128M");
    $dompdf->render();
    $dompdf->stream("ListCreditsActivos.pdf");
?>