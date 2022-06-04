<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Comprobante</title>
    <style>
        .header {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
        }

        .header-text {
            text-align: center;
        }

        p {
            margin: 0;
        }

        .table1 {
            width: 100%
        }

        .table2 {
            width: 50%
        }

        .footer {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <div>
            <img src="http://34.70.157.41/assets/img/cedit-logo.png" width="15%" />
        </div>
        <div class="header-text">
            <p>CORPORACION CEDIT E.I.R.L.</p>
            <p>R.U.C. 20604594295</p>
            <p>AV. BALTA NRO. 424 INT. 203-CHICLAYO</p>
            <p>(BALTA Y FRANCISCO CABRERA)</p>
            <p>Teléfono: 073 331669</p>
            <p>{{mb_strtoupper($data['voucher_title'])}}</p>
            <p>{{$data['voucher']}}</p>
        </div>
    </div>
    <div class="table">
        <div class="row">
            Fecha de Emisión: {{$data['date']}}
        </div>
        <hr>
        <div class="row">
            Sr.(es): {{$data['client']['name']}}
        </div>
        <div class="row">
            {{$data['voucher_type'] !=  'F' ? 'DNI' : 'RUC'}}: {{$data['client']['dni']}}
        </div>
        <table class="table1">
            <thead>
                <tr>
                    <td>Ctd</td>
                    <td>Descripción</td>
                    <td>Pr. Un.</td>
                    <td>Total</td>
                </tr>
            </thead>
            <tbody>
                @foreach($data['detail'] as $item)
                <tr>
                    <td>1.00</td>
                    <td>{{$item['label']}}</td>
                    <td>{{number_format($item['amount'], 2)}}</td>
                    <td>{{number_format($item['amount'], 2)}}</td>
                </tr>
                @endforeach
                <tr>
                    <td style="text-align: center" colspan="4">Total S/.{{number_format($data['total']['amount'], 2)}}
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center" colspan="4">{{$data['total']['label']}}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div>
        <table class="table2">
            <tr>
                <td>Subtotal</td>
                <td>S/ {{number_format($data['total']['amount'], 2)}}</td>
            </tr>
            <tr>
                <td>Inafecto I.G.V.</td>
                <td>S/ 0.00</td>
            </tr>
            <tr>
                <td>Importe Total</td>
                <td>S/ {{number_format($data['total']['amount'], 2)}}</td>
            </tr>
        </table>
        <p>Vendedor: {{$data['responsable']}}</p>
    </div>

    <div class="footer">
	<br>
	<br>
	<br>
	<br>
	<br>
        <p>AVISO IMPORTANTE</p>
        <p>SI EL ALUMNO SE RETIRARA EN FORMA</p>
        <p>VOLUNTARIA, LA INSTITUCIÓN NO SE HACE</p>
	<p>RESPONSABLE DE CAMBIOS NI DEVOLUCIONES.</p>
	<br>
	<br>
        <p>LA DIRECCIÓN</p>
    </div>
</body>

</html>
