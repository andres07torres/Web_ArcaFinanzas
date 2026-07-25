import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";

/* if you're using React */
// import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        /* react(), // if you're using React */
        symfonyPlugin({
            originOverride: process.env.VITE_ORIGIN || "http://localhost:5173",
        }),
    ],
    server: {
        host: process.env.VITE_HOST || "localhost",
        port: Number(process.env.VITE_PORT) || 5173,
        origin: process.env.VITE_ORIGIN || "http://localhost:5173",
    },
    build: {
        rollupOptions: {
            input: {
                app: "./assets/app.js"
            },
        }
    },
});
