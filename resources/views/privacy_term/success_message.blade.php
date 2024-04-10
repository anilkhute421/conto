<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <style>
        @import url(https://fonts.googleapis.com/css?family=Raleway:300,400,600);


        body {
            margin: 0;
            font-size: .9rem;
            font-weight: 400;
            line-height: 1.6;
            color: #212529;
            text-align: left;
            background-color: #f5f8fa;
        }

        .my-form {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }

        .my-form .row {
            margin-left: 0;
            margin-right: 0;
        }

        /*.login-form {*/
        /*    padding-top: 1.5rem;*/
        /*    padding-bottom: 1.5rem;*/
        /*}*/

        .login-form .row {
            margin-left: 0;
            margin-right: 0;
        }

        header.header-main {
            width: 100%;
            display: flex;
            text-align: center;
            justify-content: center;
            align-items: center;
            min-height: auto;
        }

        .header-main {
            font-size: 28px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 20px;
            background: #145DA0;
            height: 80px;
            display: flex;
            align-items: center;

        }

        header.header-main img {
            width: 150px;
        }

        .card-header {
            background-color: #145DA0;
            color: white;
            text-align: center;
            font-weight: bold;
            font-size: 15px;


        }
        .submit-btn{
            display: flex;
            background: #145DA0;
            color: white;
padding: 10px 50px;

        }
    </style>
    <script>
        function validateForm() {
            var password = document.forms["myForm"]["password"].value;
            var confirmPassword = document.forms["myForm"]["confirmPassword"].value;
            var err = document.getElementById('err')
            console.log(password, confirmPassword)
            const regex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{6,16}$/;

            if (password === "") {
                alert("password must be filled out");
                return false;
            } else if (confirmPassword === "") {
                alert("confirmPassword  must be filled out");
                return false;                
             }
                else if(!regex.test(password)){
                err.innerHTML="Password must contain at least 1 capital letter, 1 lowercase letter, 1 number and 1 special character"
                return false;
            }
                else if (password !== confirmPassword) {
                err.innerHTML = "password and confirm password is not matched "
                return false;

            }

        }
    </script>
</head>
<body>
<main class="login-form">
    <div class="cotainer">
        <div class="row justify-content-center">
           
            <div class="col-md-3">
            <b>{{ $success }}</b>
               
        </div>
    </div>
    </div>
</main>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf"
        crossorigin="anonymous"></script>

</html>