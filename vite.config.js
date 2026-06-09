import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: false
    })
  ],
  build: {
    manifest: true, // Ensure the manifest.json is generated
    outDir: 'public/build' // Output directory for the assets
  }
});
