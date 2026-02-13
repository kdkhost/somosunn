@once
    <style>
        .text-gradient {
            background: linear-gradient(135deg, var(--unn-azul-1) 0%, var(--unn-azul-3) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .unn-title-gradient {
            background: linear-gradient(90deg, #2E3192 0%, #0071BC 60%, #29ABE2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        .unn-title-max {
            max-width: 700px;
            word-break: break-word;
            margin-left: auto;
            margin-right: auto;
        }

        @media (max-width: 640px) {
            .unn-title-max {
                font-size: 2.2rem !important;
                max-width: 95vw;
            }
        }
    </style>
@endonce

