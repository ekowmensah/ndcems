<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDC Election Management System</title>
    <style>
        :root {
            --ndc-green: #0f6a44;
            --ndc-green-dark: #084a2f;
            --ndc-gold: #f7c843;
            --ndc-red: #c82032;
            --ink: #1f2937;
            --muted: #5b6472;
            --surface: #ffffff;
            --line: #e5e7eb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 12% 18%, rgba(247, 200, 67, 0.20), transparent 34%),
                radial-gradient(circle at 88% 22%, rgba(15, 106, 68, 0.16), transparent 36%),
                linear-gradient(180deg, #f8fbf9 0%, #ffffff 52%, #f4f7f6 100%);
            min-height: 100vh;
        }
        .hero {
            max-width: 1060px;
            margin: 0 auto;
            padding: 42px 20px 28px;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: #0f5132;
            letter-spacing: 0.2px;
            margin-bottom: 18px;
        }
        .brand-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--ndc-red), var(--ndc-gold));
            box-shadow: 0 0 0 4px rgba(15, 106, 68, 0.12);
        }
        .hero-panel {
            background: linear-gradient(130deg, var(--ndc-green), var(--ndc-green-dark));
            border-radius: 20px;
            color: #fff;
            padding: 34px 28px;
            box-shadow: 0 14px 28px rgba(8, 74, 47, 0.22);
            position: relative;
            overflow: hidden;
        }
        .hero-panel::after {
            content: "";
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            right: -72px;
            top: -90px;
            background: radial-gradient(circle, rgba(247, 200, 67, 0.35) 0%, rgba(247, 200, 67, 0) 72%);
        }
        .hero h1 {
            margin: 0 0 12px;
            font-size: clamp(1.6rem, 3.4vw, 2.55rem);
            line-height: 1.2;
            max-width: 760px;
            position: relative;
            z-index: 1;
        }
        .hero p {
            margin: 0;
            max-width: 760px;
            line-height: 1.6;
            color: rgba(255,255,255,0.92);
            position: relative;
            z-index: 1;
        }
        .grid {
            max-width: 1060px;
            margin: 0 auto;
            padding: 24px 20px 34px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 18px 16px;
            box-shadow: 0 5px 16px rgba(15, 23, 42, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
        }
        .card h3 {
            margin: 0 0 8px;
            font-size: 1.03rem;
        }
        .card p {
            margin: 0 0 14px;
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.5;
        }
        .btn {
            display: inline-block;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.88rem;
            border-radius: 10px;
            padding: 10px 14px;
            border: 1px solid transparent;
        }
        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--ndc-green), #0b5a39);
        }
        .btn-gold {
            color: #2c2410;
            background: linear-gradient(135deg, var(--ndc-gold), #f1b70c);
        }
        .btn-outline {
            color: var(--ndc-green-dark);
            border-color: #b8d4c7;
            background: #f4faf7;
        }
        .foot {
            max-width: 1060px;
            margin: 0 auto;
            padding: 0 20px 28px;
            color: #6b7280;
            font-size: 0.83rem;
        }
        @media (max-width: 980px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <section class="hero">
        <div class="brand">
            <span class="brand-dot"></span>
            NDC Election Management System
        </div>
        <div class="hero-panel">
            <h1>Centralized Monitoring For Electoral Results</h1>
            <p>
                Access live presidential and parliamentary result dashboards, and move into the secured operations area for data entry and validation workflows.
            </p>
        </div>
    </section>

    <section class="grid">
        <article class="card">
            <h3>Parliamentary Results</h3>
            <p>View constituency-level parliamentary result summaries and trends.</p>
            <a class="btn btn-primary" href="{{ route('parliament') }}">Open Parliament Dashboard</a>
        </article>
<!--
        <article class="card">
            <h3>Presidential Results</h3>
            <p>Track presidential tallies and regional breakdowns in one place.</p>
            <a class="btn btn-gold" href="{{ route('president') }}">Open President Dashboard</a>
        </article> -->

        <article class="card">
            <h3>Secure Staff Access</h3>
            <p>Sign in to capture results, review submissions, and manage operational tasks.</p>
            <a class="btn btn-outline" href="{{ route('login') }}">Go To Login</a>
        </article>
    </section>

    <div class="foot">
        Public access is limited to result dashboards. Administrative functions require authentication.
    </div>
</body>
</html>

