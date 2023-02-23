<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <title>Login project</title>
    <script src="https://kit.fontawesome.com/585fda3aab.js" crossorigin="anonymous"></script>
<body>
    <style>
        *{
    padding: 0;
    margin: 0;
    outline: 0;
    box-sizing: border-box;
    font-family: 'Varela Round', sans-serif;
}


body {
    display: flex;
    height: 100vh;
    align-items: center;
    justify-content: center;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' version='1.1' xmlns:xlink='http://www.w3.org/1999/xlink' xmlns:svgjs='http://svgjs.com/svgjs' width='1440' height='560' preserveAspectRatio='none' viewBox='0 0 1440 560'%3e%3cg mask='url(%26quot%3b%23SvgjsMask1000%26quot%3b)' fill='none'%3e%3crect width='1440' height='560' x='0' y='0' fill='%231e3266'%3e%3c/rect%3e%3cpath d='M1512 560L0 560 L0 299.5Q62.8 242.3%2c 120 305.1Q194.17 259.27%2c 240 333.44Q277.91 251.35%2c 360 289.26Q411.91 269.17%2c 432 321.08Q510.14 279.22%2c 552 357.35Q561.09 294.44%2c 624 303.52Q691.39 250.91%2c 744 318.31Q827.25 281.56%2c 864 364.82Q865.57 294.39%2c 936 295.96Q1001.12 241.07%2c 1056 306.19Q1130.6 260.79%2c 1176 335.39Q1226.63 314.01%2c 1248 364.64Q1255.9 300.54%2c 1320 308.45Q1373.63 242.07%2c 1440 295.7Q1489.54 273.24%2c 1512 322.78z' fill='%23182f5d'%3e%3c/path%3e%3cpath d='M1536 560L0 560 L0 397.24Q23.48 348.72%2c 72 372.2Q113.72 341.92%2c 144 383.64Q165.23 332.87%2c 216 354.1Q281.24 299.34%2c 336 364.58Q402.27 310.85%2c 456 377.12Q538.02 339.14%2c 576 421.16Q623.14 396.3%2c 648 443.44Q651.8 375.24%2c 720 379.03Q766.15 305.18%2c 840 351.33Q942.97 334.3%2c 960 437.28Q996.34 353.62%2c 1080 389.95Q1097.55 335.5%2c 1152 353.05Q1222.33 303.38%2c 1272 373.71Q1316.93 346.64%2c 1344 391.58Q1393.03 368.61%2c 1416 417.64Q1483.88 365.51%2c 1536 433.39z' fill='%2325467d'%3e%3c/path%3e%3cpath d='M1512 560L0 560 L0 473.62Q65.73 419.35%2c 120 485.07Q189.12 434.19%2c 240 503.3Q263.71 407.01%2c 360 430.73Q438.83 389.56%2c 480 468.39Q532.44 448.83%2c 552 501.28Q589.32 418.6%2c 672 455.92Q760.7 424.62%2c 792 513.32Q811.8 413.13%2c 912 432.93Q954.03 402.96%2c 984 444.99Q1052.97 441.96%2c 1056 510.93Q1071.67 454.61%2c 1128 470.28Q1173.62 443.9%2c 1200 489.52Q1242.91 412.43%2c 1320 455.34Q1341.87 405.2%2c 1392 427.07Q1474.84 389.9%2c 1512 472.74z' fill='%23356cb1'%3e%3c/path%3e%3cpath d='M1488 560L0 560 L0 528.31Q48.02 504.33%2c 72 552.35Q149.81 510.16%2c 192 587.97Q203.98 527.95%2c 264 539.93Q286.75 490.67%2c 336 513.42Q427.82 485.24%2c 456 577.06Q470.05 519.11%2c 528 533.16Q569.76 502.92%2c 600 544.69Q645.87 470.56%2c 720 516.42Q786.75 463.17%2c 840 529.93Q918.46 488.38%2c 960 566.84Q963.56 498.4%2c 1032 501.97Q1062.9 460.88%2c 1104 491.78Q1185.05 452.83%2c 1224 533.88Q1282.7 520.59%2c 1296 579.29Q1320.54 483.83%2c 1416 508.36Q1458.71 479.08%2c 1488 521.79z' fill='white'%3e%3c/path%3e%3c/g%3e%3cdefs%3e%3cmask id='SvgjsMask1000'%3e%3crect width='1440' height='560' fill='white'%3e%3c/rect%3e%3c/mask%3e%3c/defs%3e%3c/svg%3e");
    background-repeat: no-repeat; 
    background-size: cover;
    animation: move 50s linear alternate infinite;
}


@keyframes move{
    50%{
        background-position: 50%;
    }
}



.login-box{
    height: min-content;
    width: 400px;
    border-radius: 25px;
    background-color: #ffff;
    padding: 40px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 2);
    text-align: center;
}

.login-box h2{
    font-size: 2.2rem;
}


.login-box p {
    color: #404040;
    margin-top: 4px;
    font-size: 1.2rem;
}

form{
    text-align: left;
    margin: top 30px; 
}

form .form-group{
    margin: 18px 0;
}

form .form-group label {
    display: block;
    font-size: 20px; 
    color: #404040;
}

form .form-group input {
    width: 100%;
    height: 40px;
    margin-top: 6px;
    padding: 0 12px 0 34px;
    border: 2px solid #c4c4c4;
    border-radius: 20px;
    font-size: 18px;
    color: #404040;
    transition: 1ms;
}


form .form-group input:focus{
    border-color: #124ce7;
    box-shadow: 0 0 12px #bbd3ff;
}


form .form-group input:valid{
    border-color: #124ce7;
    background: #e4edff;
}


.form-group i {
    position: absolute;
    color: #404040;
    transform: translateX(12px) translateY(16px);
    font-size: 20px;
}

.forgot{
    cursor:pointer ;
    color: #3784ff;
    font-size: 18px;
    text-decoration: none;
    margin-top: px;
}

 .forgot:hover {
    color: #001e81
}


.btn{  
    border-radius: 4px;
    height: 44px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    transition : all 200ms linear;
    transition: all 200ms linear;
    padding: 0 30px;
    letter-spacing: 1px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    border: none;
    background-color: #124ce7;
    color: #ffffff;
    box-shadow: 0 8px 24px 0 rgba(255,235,167,.2);
    text-decoration: none;
    border-radius: 25px;
    cursor: pointer;
    margin-top: 2px;
    margin-right: auto;
    margin-bottom: 15px;
  }
  .btn:active,
  .btn:focus{  
    background-color: #ffffff;
    color: #124ce7;
    box-shadow: 0 8px 24px 0 rgba(16,39,112,.2);
  }
  .btn:hover{  
    background-color: #ffffff;
    color: #124ce7;
    box-shadow: 0 8px 24px 0 rgba(16,39,112,.2);
  }
  a {
	cursor: pointer;
  transition: all 200ms linear;}
    </style>
      <img src="{{ asset('storage/public/images/profile/avatar.png') }}" alt="" title=""></a>
    <div class="login-box">
        <h2>Welcome back</h2>
        <p>Enter your details</p>

        <form>
            <div class="form-group">
                <label for="email">Email</label>
                <i class="fa-solid fa-user"></i>
                    <input type="email" id="email" placeholder="email" required>
            </div>
             <div class="form-group">
                <label for="password">Password</label>
                <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" placeholder="Your password" required>
             </div>
        </form>
        <div>
            <a href="#" class="btn">Log In</a>
            </div>
            <div>
            <a href="#" class="forgot">
                Forgot Your Password?
            </a>
            </div>
            <img src="{{asset('/storage/test.png/dgFtYoj05lLemeRGqslNUvGHzkrhwUnEDVhxxkK7.png')}}" alt="" title=""></a>
            <img src="{{asset('/storage/test.png/dgFtYoj05lLemeRGqslNUvGHzkrhwUnEDVhxxkK7.png')}}" alt="Cinque Terre" width="1000" height="300">   
    </div>
</body>
</html>