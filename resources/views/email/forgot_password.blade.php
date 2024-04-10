<html>

<head>
    <title>contolio</title>
</head>

<body>
	<div align="center">
	    <table style="width:680px; font-family:Arial, Helvetica, sans-serif; background-color: #fff;">
	        <tbody style="box-shadow: 0 0 5px rgba(0,0,0,0.2);">

		        <tr align="center" >
		            <td  style="background-color: #efefef; padding: 0 10px 10px;text-align: left;">
		                <h3 style="font-size:25px; color:#5B003C; margin-top:5%; padding:0px 10px; font-weight: 500;">
		                    <span style="color: #5B003C;">Forgot Password</span>
		                </h3>
		                <p style="font-size:20px; line-height:24px; color:#353535; padding:0px 10px 10px; margin: 0;">Hi
		                    </p>
		                <p style="font-size:17px; line-height:24px; color:#57697e; padding:0px 10px 5px; margin: 0 0 15px;">
		                    We are sending this email because you requested a password reset
		                </p>
		                <p style="font-size:17px; line-height:24px; color:#57697e; padding:0px 10px 5px; margin: 0 0 15px;">
		                    Newly Temporarily Generated password is as below to sign in and reset it
		                </p>
		                <p style="font-size:20px; line-height:24px; color:#353535; padding:0px 10px 10px; margin: 0;">{{ $emailData['password']}}
						</p>
		                <p style="font-size:17px; line-height:24px; color:#57697e; padding:10px 10px; margin-top: 50px; text-align: left;">
		                    Thanks, <br> Contolio Team</p>
		            </td>
		        </tr>
	        </tbody>
	    </table>
	</div>
</body>

</html>
