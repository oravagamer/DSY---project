import {defineConfig} from 'vite'
import react from '@vitejs/plugin-react'
import {svgrComponent} from 'vite-plugin-svgr-component';
import babel from '@rollup/plugin-babel';


// https://vitejs.dev/config/
export default defineConfig({
    plugins: [react(), svgrComponent(), babel({
      babelHelpers: 'bundled',
      plugins: ['@babel/plugin-proposal-throw-expressions'],
    })], server: {
        proxy: {
            "/rest/api": {
                target: "http://localhost:80",
                changeOrigin: true,
                secure: false,
                xfwd: true,
                rewrite: path => path.replace("/rest/api", "/DSY---project/backend/rest/api"),
                configure: (proxy, _options) => {
                    proxy.on('error', (err, _req, _res) => {
                        console.log('proxy error', err);
                    });
                    proxy.on('proxyReq', (proxyReq, req, _res) => {
                        console.log('Sending Request to the Target:', req.method, req.url);
                    });
                    proxy.on('proxyRes', (proxyRes, req, _res) => {
                        console.log('Received Response from the Target:', proxyRes.statusCode, req.url);
                    });
                }
            }
        }, port: 5175
    }
})
