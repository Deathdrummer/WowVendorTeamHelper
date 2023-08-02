import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import inject from "@rollup/plugin-inject";
import dynamicImport from 'vite-plugin-dynamic-import';

const path = require('path');

export default defineConfig({
    plugins: [
        laravel({
            input: [
            	'resources/sass/common/app.sass',
				'resources/sass/site.sass',
				'resources/sass/admin.sass',
				'resources/js/site.js',
				'resources/js/admin.js',
			],
            refresh: ['resources/views/**'],
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