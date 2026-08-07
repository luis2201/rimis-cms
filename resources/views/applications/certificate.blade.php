<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        html, body { margin: 0; padding: 0; width: 210mm; height: 297mm; }
        body { font-family: "DejaVu Serif", serif; color: #111; }
        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: -1;
        }
        .content {
            position: absolute;
            top: 82mm;
            left: 29mm;
            width: 152mm;
            font-size: 12.5pt;
            line-height: 1.55;
            text-align: justify;
        }
        .recipient { margin: 0 0 8mm; font-weight: bold; }
        p { margin: 0 0 5mm; }
        .details { margin: 7mm 0; }
        .details p { margin: 1.5mm 0; }
        .signature-block {
            position: absolute;
            top: 214mm;
            left: 57mm;
            width: 96mm;
            text-align: center;
            font-size: 10.5pt;
            line-height: 1.3;
        }
        .signature { height: 18mm; margin-bottom: -4mm; }
        .stamp {
            position: absolute;
            top: 209mm;
            left: 33mm;
            width: 31mm;
            opacity: .82;
            transform: rotate(-8deg);
        }
        .line { border-top: 1px solid #222; margin: 0 auto 2mm; width: 82mm; }
        .director { font-weight: bold; }
    </style>
</head>
<body>
    <img class="background" src="{{ resource_path('images/certification/background.jpeg') }}" alt="">

    <main class="content">
        <p class="recipient">A QUIEN CORRESPONDA:</p>

        <p>
            Por medio de la presente, el <strong>INSTITUTO SUPERIOR TECNOLÓGICO PORTOVIEJO</strong>
            certifica que {{ $article }} <strong>{{ $salutation }} {{ $name }}</strong> es miembro activo de la
            <strong>Red de Investigación Multidisciplinaria Independiente Scio (RIMIS)</strong>.
        </p>

        <p>{{ ucfirst($article) }} {{ $memberNoun }} se encuentra {{ $registered }} bajo las siguientes condiciones:</p>

        <div class="details">
            <p><strong>Rol:</strong> {{ $role }}</p>
            <p><strong>Línea de trabajo:</strong> {{ $researchLine }}</p>
        </div>

        <p>
            Se expide esta constancia a solicitud {{ $interestedPhrase }} en {{ $city }},
            el {{ $day }} de {{ $month }} de {{ $year }}.
        </p>
    </main>

    <img class="stamp" src="{{ resource_path('images/certification/stamp.png') }}" alt="">
    <section class="signature-block">
        <img class="signature" src="{{ resource_path('images/certification/signature.png') }}" alt="">
        <div class="line"></div>
        <div class="director">DR. ROBERTH ZAMBRANO SANTOS</div>
        <div>DIRECTOR DE LA RED DE INVESTIGACIÓN RIMIS</div>
    </section>
</body>
</html>
