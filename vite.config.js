import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import inject from "@rollup/plugin-inject";
import dynamicImport from 'vite-plugin-dynamic-import';
import vue from '@vitejs/plugin-vue';

const path = require('path');

export default defineConfig({
    plugins: [
		vue(),
        laravel({
            input: [
            	'resources/sass/common/app.sass',
				'resources/sass/site.sass',
				'resources/sass/admin.sass',
				'resources/js/site.js',
				'resources/js/admin.js',
			],
            refresh: [
				'resources/views/admin/section/*',
				'resources/views/admin/section/*/*',
				'resources/views/admin/layout/**',
				'resources/views/site/section/*',
				'resources/views/site/section/*/*',
				'resources/views/site/layout/**',
			],
        }),
        inject({
			$: 'jquery',
			jQuery: 'jquery',
		}),
		dynamicImport(/* options */),
    ],
	commonjsOptions: {
		esmExternals: true 
	},
	resolve: {
		alias: {
			'@': path.resolve(__dirname, 'resources/js'),
			'@plugins': path.resolve(__dirname, 'resources/js/plugins'),
			'@sass': path.resolve(__dirname, 'resources/sass'),
			'@fonts': path.resolve(__dirname, 'resources/fonts'),
		}
	},
	define: {
		'process.env': {}
	},
});