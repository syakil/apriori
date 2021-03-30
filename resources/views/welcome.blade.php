<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <!-- Styles -->
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
        @if ($message = Session::get('error'))
            <script>
                var pesan = "{{$message}}"
                swal("Maaf !", pesan, "error"); 
            </script>
        @elseif ($message = Session::get('success'))
            <script>
                var pesan = "{{$message}}"
                swal("Selamat !", pesan, "success"); 
            </script>
        @endif
            <div class="content">
                <div class="title m-b-md">
                    Algoritma Apriori
                </div>

                <div class="links">
                <form action="{{route('apriori.proses')}}" method="post">
                {{ csrf_field() }}
                    <div class="form-group">
                        <label for="min_confidence">Minimal Confidence</label>
                        <input required type="number" name="min_confidence" class="form-control" id="min_confidence" placeholder="Minimal Nilai Confidence">
                    </div>
                    <div class="form-group">
                        <label for="min_support">Minimal Support</label>
                        <input required type="number" name="min_support" class="form-control" id="min_support" placeholder="Minimal Support">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                </div>
            </div>
        </div>
    </body>
</html>
