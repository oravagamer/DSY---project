import {defineConfig} from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [react()],
    server: {
        proxy: {
            "/rest/api": {
                target: "http://localhost:80",
                changeOrigin: true,
                secure: false,
                rewrite: path => path.replace("/rest/api", "/DSY---project/rest/api"),
            }
        },
        port: 5175
    }
})
