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
		                    <span style="color: #5B003C;">Dear {{$emailData['tenant_name']}}</span>
		                </h3>
		                <p style="font-size:20px; line-height:24px; color:#353535; padding:0px 10px 10px; margin: 0;">
                            Property Manager {{$emailData['pm_company_name']}} has created your account to view and manage your unit    
                        </p>
		                <p style="font-size:17px; line-height:24px; color:#57697e; padding:0px 10px 5px; margin: 0 0 15px;">
		                    Your Username is: {{$emailData['tenant_email']}} ,<br>
                           Your Password is: {{$emailData['password']}} 
                        </p>
		                
		                
                        <p style="font-size:17px; line-height:24px; color:#57697e; padding:0px 10px 5px; margin: 0 0 15px;">
                            Please download the app from app stores and login to your account 
                        </p>
                        <span style="font-size:17px; line-height:24px; color:#57697e; padding:0px 10px 5px; margin: 0 0 15px;">
                            Apple store link: <a href="https://apps.apple.com/us/app/contolio/id1618167766">https://apps.apple.com/us/app/contolio/id1618167766</a><br>
                        </span>
						<span style="font-size:17px; line-height:24px; color:#57697e; padding:0px 10px 5px; margin: 0 0 15px;">
                            Google play link: <a href="https://play.google.com/store/apps/details?id=com.contolios">https://play.google.com/store/apps/details?id=com.contolios</a>
                        </span>
		                <p style="font-size:17px; line-height:24px; color:#57697e; padding:10px 10px; margin-top: 15px; text-align: left;">
		                    Thanks, <br> Contolio Team</p>
		            </td>
		        </tr>
	        </tbody>
	    </table>
	</div>
</body>

</html>