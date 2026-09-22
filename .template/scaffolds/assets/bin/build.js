import esbuild from 'esbuild'

const isDev = process.argv.includes('--dev')

const context = await esbuild.context({
    entryPoints: [
        './resources/js/index.js',
        './resources/css/index.css',
    ],
    outdir: './resources/dist',
    bundle: true,
    mainFields: ['module', 'main'],
    platform: 'neutral',
    sourcemap: isDev ? 'inline' : false,
    sourcesContent: isDev,
    treeShaking: true,
    target: ['es2020'],
    minify: !isDev,
})

if (isDev) {
    await context.watch()
} else {
    await context.rebuild()
    await context.dispose()
}
