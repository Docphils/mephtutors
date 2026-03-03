<div>
    <style>
        .bt-header {
            position: relative;
            background-image: url('/images/bootcamp-header.jpeg');
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
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

            .bt-form-box {
                margin-top: 5rem;
                min-height: 350px;
            }

            .bt-header {
                height: 100vh;
            }
        }
    </style>

    <div class="bt-header">
        <div class="bt-overlay">
            <div class="bt-side-text">
                <p style="font-size: 0.9rem;">The beginner friendly Web Development BootCamp</p>
                <h1>Become a Proficient Programmer in Weeks</h1>
                <p>Class starts October 27, 2025</p>
            </div>
            <div class="bt-form-box">
                <h2 class="text-xl font-semibold">Enroll Now!</h2>
                <div class="bt-input">
                    <livewire:enrollment-form :service-item-slug="$serviceItemSlug ?? null" />
                </div>
            </div>
        </div>
    </div>

    <div class="bt-contact-bar bg-slate-900">
        To speak with our support staff, call <strong>(+234) 80-628-691-70</strong>
    </div>

    <section class="bt-section bg-slate-50 text-slate-700">
        <h2 class="text-2xl font-semibold text-slate-900">Why Learn Web Development With Us?</h2>
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
                <i class="fa-solid fa-clock text-cyan-600 mb-1 text-4xl"></i>
                <p>Enjoy a flexible learning schedule designed for busy students, workers, and entrepreneurs.</p>
            </div>

            <div class="bt-feature w-full mx-auto">
                <i class="fa-solid fa-business-time text-cyan-600 mb-1 text-4xl"></i>
                <p>Build a job-ready portfolio, work on live projects, and get guidance to start freelancing or land a tech job.</p>
            </div>
        </div>
    </section>
</div>
