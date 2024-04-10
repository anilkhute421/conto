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
                            <span style="color: #5B003C;">Dear Expert</span>
                        </h3>
                        <p style="font-size:20px; line-height:24px; color:#353535; padding:0px 10px 10px; margin: 0;">
                            Maintenance Request Details:
                        </p>
                        <p
                            style="font-size:17px; line-height:24px; color:#57697e; padding:0px 10px 5px; margin: 0 0 15px;">
                            Tenant Name:{{ $emailData['tenant_name'] }},<br>
                            Tenant phone:{{ $emailData['tenant_phone'] }},<br>
                            Buildings:{{ $emailData['building_name'] }},<br>
                            Unit No: {{ $emailData['unit_no'] }},<br>
                            Request ID: {{ $emailData['request_id'] }},<br>
                            Request for: {{ $emailData['request_for'] }},<br>
                            Visit Date Time: {{ $emailData['visit_datetime'] }},<br>
                            Description: {{ $emailData['description'] }},<br>
                            <b>Status: {{ $emailData['status'] }}</b>,<br>
                            Address: {{ $emailData['address'] }},<br>
                            URL : {{ $emailData['file_details'] }}<br>
                        </p>

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
