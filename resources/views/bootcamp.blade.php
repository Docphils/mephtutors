<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MephEd Learning - Enroll</title>
     <!-- Fonts -->
     <link rel="preconnect" href="https://fonts.bunny.net">
     <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />

    <meta property="og:title" content="MephEd Learning - Learn top educational and technological skills with MephEd" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://www.mephed.ng/bootcamp" />
    <meta property="og:site_name" content="MephEd Learning" />
    <meta property="og:image" content="https://mephed.ng/images/banner.jpg" />
    <meta property="og:image:type" content="image/png">
    <meta property="og:description" content="Enroll to learn in-demand educational and tech skills now!" />

    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@mephed" />
    <meta name="twitter:title" content="MephEd Learning - Learn top educational and technological skills with MephEd" />
    <meta name="twitter:description" content="Enroll to learn in-demand educational and tech skills now!" />
    <meta name="twitter:image:src" content="https://mephed.ng/images/banner.jpg" />
    <meta property="twitter:image:type" content="image/png">
    <meta name="twitter:domain" content="https://www.mephed.ng/bootcamp" />

     <style>
        body {
          margin: 0;
          font-family: Arial, sans-serif;
          background-color: #ffffff;
        }

        .bt-header {
          position: relative;
          background-image: url('/images/bootcamp-header.jpeg'); /* Replace with your background */
          background-size: cover;
          background-position: center;
          height: 400px;
          color: white;
          padding-top: 1.5rem;
        }

        .bt-overlay {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background-color: rgba(0, 0, 0, 0.3);
          display: flex;
          align-items: center;
          justify-content: space-around;
          padding: 2rem;
          box-sizing: border-box;
        }

        .bt-form-box {
          background: white;
          color: black;
          padding: 0.5rem;
          border-radius: 8px;
          max-width: 400px;
          box-shadow: 0 4px 8px rgba(0,0,0,0.3);
          height: 100%;
          overflow: auto;
          -ms-overflow-style: none;
          scrollbar-width: none;
        }

        .bt-form-box h2 {
          margin-bottom: 0.5rem;

        }

        .bt-input {
          width: 100%;
          padding: 0.5rem;
          margin-bottom: 0.75rem;
          border: 1px solid #ccc;
          border-radius: 4px;
        }


        .bt-side-text {
          max-width: 400px;
          margin-left: 2rem;
        }

        .bt-side-text h1 {
          font-size: 2rem;
          margin-bottom: 0.5rem;
        }

        .bt-side-text p {
          font-size: 1rem;
        }

        .bt-contact-bar {
          //background-color: #003366;
          color: white;
          text-align: center;
          padding: 0.75rem;
          font-weight: bold;
        }

        .bt-section {
          padding: 3rem 1rem;
          text-align: center;
        }

        .bt-section h2 {
          color: #003366;
          margin-bottom: 2rem;
        }

        .bt-feature-grid {
          display: grid;
          margin: 0 auto;
        }

        .bt-feature {
          max-width: 300px;
          text-align: left;
        }


        @media (max-width: 600px) {
          .bt-overlay {
            flex-direction: column;
          }

          .bt-side-text {
            margin: 1.5rem 0 0 0;
          }

          .bt-form-box{
            margin-top: 5rem;
            min-height: 350px
          }
          .bt-header{
            height: 100vh;
          }
        }
      </style>

     <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles 

    <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17506686809">
        </script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'AW-17506686809');
        </script>     
</head>

    <body class="bt-body  font-sans leading-normal tracking-normal">
        <!-- Header Section -->
        <header x-data="{ open: false }" class="bg-gradient-to-r from-sky-500 to-cyan-600 text-white py-6 shadow-lg">
            <div class="container mx-auto flex justify-center items-center px-6">
                <a href="{{ url('/') }}" wire:navigate class="mr-2">
                    <img src="/images/MephEd.png" alt="Logo Image" class="object-cover h-6 sm:h-8 w-24 sm:w-32">
                </a>
                <div class="flex text-xl sm:text-3xl font-semibold"> Learning</div>
                
            </div>
            
        </header>

        <!-- Contact Form Section -->
        <div class="bt-header">
            <div class="bt-overlay">
            <div class="bt-side-text">
                <p style="font-size: 0.9rem;">The beginner friendly Web Development BootCamp</p>
                <h1>Become a Proficient Programmer in Weeks</h1>
                <p>Class starts October 27, 2025</p>
            </div>
                        <div class="bt-form-box">
                <h2 class="text-xl font-semibold">Enroll Now!</h2>
                <div class="bt-input"><livewire:bootcamps />
                </div>
            </div>
            </div>
        </div>

        <div class="bt-contact-bar bg-cyan-600">
            To speak with our support staff, call <strong>(+234) 80-628-691-70</strong>
        </div>

        <section class="bt-section">
            <h2 class="text-2xl font-semibold">Why Learn Web Development With Us?</h2>
            <div class="bt-feature-grid grid grid-cols-2 justify-center gap-6">
              <div class="bt-feature w-full mx-auto">
                <i class="fa-solid fa-file-code text-cyan-600 mb-1 text-4xl"></i>
                <p>Start from scratch and build full websites using beginner-friendly tools and frameworks.</p>
              </div>

              <div class="bt-feature w-full mx-auto">
                <i class="fa-solid fa-chart-diagram text-cyan-600 mb-1 text-4xl"></i>
                <p>Learn practical web skills like HTML, CSS, JavaScript, React, Node.js, and Git with real-life use cases.</p>
              </div>

              <div class="bt-feature w-full mx-auto">
                <i class="fa-solid fa-alarm-clock text-cyan-600 mb-1 text-4xl"></i>
                <p>Enjoy a flexible learning schedule designed for busy students, workers, and entrepreneurs.</p>
              </div>

              <div class="bt-feature w-full mx-auto">
                <i class="fa-solid fa-business-time text-cyan-600 mb-1 text-4xl"></i>
                <p>Build a job-ready portfolio, work on live projects, and get guidance to start freelancing or land a tech job.</p>
              </div>
            </div>
        </section>
        

                    


        @livewireScripts
    </body>
</html>