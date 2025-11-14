import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

// Helper: resolve LAN host for HMR so other devices can access dev server
function resolveHmrHost(env){
  // Priority: explicit VITE_HMR_HOST -> system detected LAN -> fallback 'localhost'
  if (env.VITE_HMR_HOST) return env.VITE_HMR_HOST;
  // Try simple LAN detection (best-effort) – Node may not always provide os.networkInterfaces inside constrained env
  try {
    const nets = Object.values(require('os').networkInterfaces());
    for (const list of nets) {
      for (const ni of list) {
        if (ni && ni.family === 'IPv4' && !ni.internal) {
          return ni.address; // first non-internal IPv4
        }
      }
    }
  } catch(_) {}
  return 'localhost';
}

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const hmrHost = resolveHmrHost(env);
  const hmrPort = Number(env.VITE_HMR_PORT || 5173);
  const hmrProtocol = env.VITE_HMR_PROTOCOL || 'http';
  const useHttps = hmrProtocol === 'https';

  return {
    plugins: [
      laravel({
        input: ['resources/css/app.css', 'resources/js/app.js'],
        refresh: true,
      }),
    ],
    server: {
      host: '0.0.0.0', // expose to LAN
      port: 5173,
      strictPort: true,
      https: useHttps,
      hmr: {
        host: hmrHost,
        port: hmrPort,
        protocol: hmrProtocol,
      },
    },
  };
});
