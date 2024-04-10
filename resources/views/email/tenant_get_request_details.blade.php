<html>

<head>
    <title>contolio</title>
</head>

<body>
    <div align="center">
        <table style="width:680px; font-family:Arial, Helvetica, sans-serif; background-color: #fff;">
            <tbody style="box-shadow: 0 0 5px rgba(0,0,0,0.2);">

                <tr align="center">
                    <td style="background-color: #efefef; padding: 0 10px 10px;text-align: left;">
                        <h3 style="font-size:25px; color:#5B003C; margin-top:5%; padding:0px 10px; font-weight: 500;">
                            <span style="color: #5B003C;">Dear {{ $emailData['tenant_name'] }}</span>
                        </h3>
                        <p style="font-size:20px; line-height:24px; color:#353535; padding:0px 10px 10px; margin: 0;">
                            {{ ucfirst( $emailData['pm_company_name'])}} has created maintenance request as below:
                        </p>
                        <p
                            style="font-size:17px; line-height:24px; color:#57697e; padding:0px 10px 5px; margin: 0 0 15px;">
                            Buildings:{{ $emailData['buildings_name'] }} ,<br>
                            Unit No: {{ $emailData['unit_no'] }} ,<br>
                            Maintance Request for: {{ $emailData['request_for'] }},<br>
                            <b>Status: {{ $emailData['status'] }}</b>,<br>
                            {{-- Property Manager: {{ $emailData['pm_name'] }},<br> --}}
                            Compnay Name: {{ $emailData['pm_company_name'] }},<br>

                        </p>

                        @if (!blank($emailData['expert']) )

                            <h4>Experts:</h4>
                            <p style="font-size:17px; line-height:24px; color:#57697e; padding:0px 10px 5px; margin: 0 0 15px;">
                            @foreach ($emailData['expert'] as $user)
                            {{ $user->name }} &nbsp;
                            {{ $user->phone }},<br>
                            @endforeach
                        </p>

                       @endif

                        <p
                            style="font-size:17px; line-height:24px; color:#57697e; padding:10px 10px; margin-top: 50px; text-align: left;">
                            Thanks, <br> Contolio Team</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
