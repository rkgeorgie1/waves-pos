@if (\App\WhatsApp::configured())
    <style>
        .whatsapp-float {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 1050;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 18px;
            border-radius: 9999px;
            background: #25d366;
            color: #ffffff;
            font: 600 13px/1 'Inter', 'Figtree', system-ui, sans-serif;
            text-decoration: none;
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.35);
            transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
        }

        .whatsapp-float:hover,
        .whatsapp-float:focus-visible {
            background: #1ebe57;
            color: #ffffff;
            outline: none;
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(37, 211, 102, 0.45);
        }

        .whatsapp-float svg {
            width: 22px;
            height: 22px;
            fill: currentColor;
        }

        @media (max-width: 575.98px) {
            .whatsapp-float {
                right: 14px;
                bottom: 14px;
                padding: 12px;
            }

            .whatsapp-float-label {
                display: none;
            }
        }
    </style>
    <a href="{{ \App\WhatsApp::chatUrl('Hi, I would like to ask about payments for Waves POS.') }}"
       class="whatsapp-float"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Chat with admin on WhatsApp"
       title="Chat with admin on WhatsApp">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M12.04 2C6.51 2 2.07 6.43 2.07 11.96c0 1.76.46 3.48 1.34 5L2 22l5.17-1.35a9.92 9.92 0 0 0 4.87 1.24h.01c5.53 0 10.47-4.43 10.47-9.96C22.52 6.43 18.09 2 12.04 2Zm0 18.15a8.18 8.18 0 0 1-4.17-1.14l-.3-.18-3.07.8.82-2.99-.2-.31a8.16 8.16 0 0 1-1.26-4.37c0-4.51 3.68-8.18 8.19-8.18 2.19 0 4.25.85 5.8 2.39a8.11 8.11 0 0 1 2.4 5.8c0 4.51-3.68 8.18-8.21 8.18Zm4.49-6.13c-.24-.12-1.44-.71-1.66-.79-.22-.09-.38-.13-.54.13-.16.25-.62.79-.76.96-.14.16-.28.18-.52.06-.24-.13-1.05-.39-2-1.24-.74-.74-1.21-1.66-1.35-1.9-.14-.25-.15-.52-.15-.75 0-.22.06-.52.25-.8.09-.09.13-.21.19-.36.06-.14.03-.27-.02-.4-.04-.13-.54-1.3-.74-1.78-.19-.46-.39-.4-.54-.4h-.46c-.16 0-.42.06-.64.3-.22.24-.85.83-.85 2.02s.87 2.35.99 2.51c.13.16 1.7 2.6 4.13 3.64.58.25 1.03.4 1.38.51.58.19 1.11.16 1.53.1.47-.07 1.45-.59 1.66-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.47-.29Z" />
        </svg>
        <span class="whatsapp-float-label">Chat with us</span>
    </a>
@endif
