const chokidar = require('chokidar');
const {exec} = require('child_process');
const {promises: fs} = require('fs');
const ignore = require('ignore');
const path = require('path');

// Nastav cestu k sledovaným složkám
const watchedFolders = [
    './app/',
    './public/',
    './core/'
];

// Nastav cestu k Docker složce
const dockerFolder = '/var/www/html/app';

// Nastav příkaz pro kopírování souborů do Dockeru
const dockerCopyCommand = 'docker cp';

// Nastav jméno Docker kontejneru
const containerName = 'tda';

// Vytvoř Watcher
const watcher = chokidar.watch([], {ignored: /^\./, persistent: true});

// Přidej sledované složky do watcheru
watchedFolders.forEach(folder => watcher.add(folder));

console.log(`Sleduji změny ve složkách: ${watchedFolders.join(', ')}`);

// Při změně souboru
watcher
    .on('add', path => compileAndCopyToDocker(path))
    .on('change', path => compileAndCopyToDocker(path))
    .on('unlink', path => removeFromDocker(path));

async function compileAndCopyToDocker(filePath) {

    // Kompilace TypeScriptu probehne pouze pokud se jedná o soubor s priponou .ts
    if (filePath.endsWith('.ts')) {
        await compileTypeScript();
    }

    const relativePath = path.relative(watchedFolders[0], filePath);
    const destination = path.join(dockerFolder, relativePath).replace(/\\/g, '/');

    exec(`${dockerCopyCommand} ${filePath} ${containerName}:${destination}`, (error, stdout, stderr) => {
        if (error) {
            console.error(`Chyba při kopírování do Dockeru: ${stderr}`);
        } else {
            console.log(`Soubor ${filePath} byl zkopírován do Dockeru.`);
        }
    });
}

async function compileTypeScript() {
    try {
        console.log('Kompiluji TypeScript...');
        // Spusť tsc (TypeScript kompilátor)
        await exec('tsc');
        console.log('Kompilace TypeScriptu dokončena.');
    } catch (error) {
        console.error(`Chyba při kompilaci TypeScriptu: ${error.message}`);
    }
}

function removeFromDocker(filePath) {
    // Implementuj podle potřeby - odstranění souboru z Dockeru
}
