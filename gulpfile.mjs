import AdmZip from 'adm-zip';
import gulp from 'gulp';
import inquirer from 'inquirer';
import cleanCSS from 'gulp-clean-css';
import terser from 'gulp-terser';
import {deleteAsync} from 'del';
import newer from 'gulp-newer';
import fs from 'fs';
import path from 'path';
import archiver from 'archiver';
import insert from 'gulp-insert';

import {exec} from 'child_process';
import {promisify} from 'util';

const execPromise = promisify(exec);

const pluginName = `where-we-are`

// Căile fișierelor sursă și de destinație
const paths = {
    svn: '../Accepted Wordpress Plugins/svn-where-we-are',
    readme: {
        src: 'readme.txt',
        dest: 'dist'
    },
    version: {
        src: 'version.json',
        dest: 'dist'
    },
    languages: {
        src: 'languages/**/*',
        dest: 'dist/languages'
    },
    css: {
        src: 'assets/css/*.css',
        dest: 'dist/assets/css'
    },
    js: {
        src: 'assets/js/*.js',
        dest: 'dist/assets/js'
    },
    php: {
        src: ['**/*.php', '!compress_php.php'],  // Exclude compress_php.php
        dest: 'dist'
    },
    images: [
        {src: 'assets/images', dest: 'dist/assets/images'},
        {src: 'assets/vendor/leaflet/images', dest: 'dist/assets/vendor/leaflet/images'}
    ],
    libs: {
        src: 'assets/vendor/**/*', // Include toate fișierele third-party
        dest: 'dist/assets/vendor'
    }
};

// const incrementType = await chooseVersionIncrement();
// const globalConfig = incrementVersion(incrementType);
let globalConfig = null;
async function bumpVersion() {
    const incrementType = await chooseVersionIncrement();
    globalConfig = incrementVersion(incrementType);
}

// Task to Prompt for Version Increment Type
async function chooseVersionIncrement() {
    const {incrementType} = await inquirer.prompt([
        {
            type: 'list',
            name: 'incrementType',
            message: 'Which part of the version would you like to increment?',
            choices: [
                {name: 'Major (1.x.x)', value: 'major'},
                {name: 'Minor (x.1.x)', value: 'minor'},
                {name: 'Patch (x.x.1)', value: 'patch'}
            ]
        }
    ]);
    return incrementType;
}

// Increment Version
function incrementVersion(type = 'patch') {
    const versionFilePath = "./version.json";
    // Citește versiunea actuală
    const versionData = JSON.parse(fs.readFileSync(versionFilePath, "utf8"));

    let [major, minor, patch] = versionData.version.split('.').map(Number);

    if (type === 'major') major++;
    if (type === 'minor') {
        minor++;
        patch = 0;
    }
    if (type === 'patch') patch++;

    const globalConfig = {
        author: '',
        uri: '',
        company: '',
        version: '',
    };
    globalConfig.newVersion = `${major}.${minor}.${patch}`;
    globalConfig.author = versionData.author;
    globalConfig.uri = versionData.uri;
    globalConfig.company = versionData.company;

    versionData.version = globalConfig.newVersion;
    fs.writeFileSync(versionFilePath, JSON.stringify(versionData, null, 4), 'utf8');
    console.log(`New version: ${globalConfig.newVersion}`);
    console.log(`- Author: ${globalConfig.author}`);
    console.log(`- Uri: ${globalConfig.uri}`);
    console.log(`- Company: ${globalConfig.company}`);

    return globalConfig;
}

// Curăță folderul dist înainte de a rula taskurile
function clean() {
    return deleteAsync(['dist']);
}

// Minimizați și mutați CSS
function styles() {
    return gulp.src(paths.css.src)
        .pipe(cleanCSS())
        .pipe(gulp.dest(paths.css.dest));
}

// Minimizați și mutați JS
function scripts() {
    return gulp.src(paths.js.src)
        .pipe(terser())
        .pipe(gulp.dest(paths.js.dest));
}

// Copiază sursele JS neminificate
function scriptsSrc() {
    return gulp.src(paths.js.src)
        .pipe(gulp.dest('dist/assets/js/src'));
}

// Copiază fișierele PHP, excluzând `compress_php.php`
function php() {
    return gulp.src([
        '**/*.php',
        '!compress_php.php',
        '!tests/**',
        '!**/tests/**',
    ])
    .pipe(gulp.dest(paths.php.dest));
}

// Copiere fișiere Composer
function copyVendor(done) {
    try {
        // 1. Șterge vendor/ existent
        execSync('rm -rf vendor/', { stdio: 'inherit' });
        console.log('✔ vendor/ șters');

        // 2. Reinstalează doar producție
        execSync('composer install --no-dev --optimize-autoloader', { stdio: 'inherit' });
        console.log('✔ Composer install --no-dev efectuat');

    } catch(e) {
        done(new Error(`⛔ Composer install eșuat: ${e.message}`));
        return;
    }

    gulp.src([
        'vendor/**/*',
        '!vendor/bin/**',
        '!vendor/**/test{,s}/**',
        '!vendor/**/Test{,s}/**',
        '!vendor/**/demo{,s}/**',
        '!vendor/**/examples/**',
        '!vendor/**/demos/**',
        '!vendor/**/docs/**',
        '!vendor/**/.git/**',
        '!vendor/**/composer.json',
        '!vendor/**/composer.lock',
        '!vendor/**/phpunit.*',
    ], {base: './'})
        .pipe(gulp.dest('dist'))
        .on('end', () => {
            // 3. Restaurează vendor cu dev pentru development
            execSync('composer install', { stdio: 'inherit' });
            console.log('✔ Composer install (cu dev) restaurat');
            done();
        });
}

// Copiază fișierele de imagini folosind `fs` pentru copiere binară
// Recursive function to copy images, handling multiple directories
function copyImagesRecursive(srcDir, destDir) {
    fs.mkdirSync(destDir, {recursive: true});

    const items = fs.readdirSync(srcDir, {withFileTypes: true});

    items.forEach(item => {
        const srcPath = path.join(srcDir, item.name);
        const destPath = path.join(destDir, item.name);

        if (item.isDirectory()) {
            copyImagesRecursive(srcPath, destPath); // Recursive call for directories
        } else if (item.isFile()) {
            fs.copyFileSync(srcPath, destPath); // Copy each file
        }
    });
}

function images(done) {
    // Loop through each image path in the paths.images array
    paths.images.forEach(pathInfo => {
        copyImagesRecursive(pathInfo.src, pathInfo.dest);
    });
    done();
}

// Task pentru copierea fișierului version.json
function copyVersion() {
    return gulp.src(paths.version.src)
        .pipe(gulp.dest(paths.version.dest));
};

function copyReadme() {
    return gulp.src(paths.readme.src)
        .pipe(gulp.dest(paths.readme.dest));
};

// Task pentru a copia folderul languages
async function copyLanguages() {
    return gulp.src(paths.languages.src, { allowEmpty: true })
        .pipe(gulp.dest(paths.languages.dest));
}

// Copiază librăriile third-party din vendor
function libs() {
    return gulp.src(paths.libs.src)
        .pipe(newer(paths.libs.dest)) // Copiază doar fișierele noi sau modificate
        .pipe(gulp.dest(paths.libs.dest));
}

// Task pentru comprimarea fișierelor PHP
// Rulează `compress_php.php` pentru a minimiza fișierele PHP în `dist`
// Task pentru comprimarea fișierelor PHP, returnând o promisiune
function compressPHP() {
    return execPromise('php compress_php.php')
        .then(({stdout, stderr}) => {
            console.log(`Output: ${stdout}`);
            if (stderr) {
                console.error(`Eroare stderr: ${stderr}`);
            }
        })
        .catch((error) => {
            console.error(`Eroare: ${error.message}`);
            throw error;
        });
}

// Task pentru a adăuga header-ul în fișierul principal PHP după compresie
async function addHeader() {
    const pluginHeader = `<?php
/*
    Plugin Name: Where we are
    Description: Set your map location on a OpenStreet MAP, and let your customers find you easy and see directions from where they are to your location.
    Requires at least: 5.0
    Requires PHP: 7.4
    Text Domain: where-we-are
    Domain Path: /languages
    Version: ${globalConfig.newVersion}
    Author: ${globalConfig.author} | AQUIS | <a href="https://www.webnou.ro/produse/WordPress/where-we-are">Where-We-Are</a> | <a href="https://shop.webnou.ro">WebNou Shop</a>
    Author URI: ${globalConfig.uri}
    Company: ${globalConfig.company}
    License: GPLv2 or later
    License URI: https://www.gnu.org/licenses/gpl-2.0.html
    */
?>`;

    return gulp.src(['dist/*.php'])
        .pipe(insert.prepend(pluginHeader)) // Adaugă header-ul
        .pipe(gulp.dest('dist'));
}

async function addReadmeHeader() {
    const readmeHeader = `=== Where we are ===
Contributors: ${globalConfig.author}
Donate link: https://www.paypal.com/donate/?hosted_button_id=97HQX5UFJFNJ2
Tags: map, location, directions, company location, where we are
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: ${globalConfig.newVersion}
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
`;

    return gulp.src(['dist/readme.txt'])
        .pipe(insert.prepend(readmeHeader)) // Adaugă header-ul
        .pipe(gulp.dest('dist'));
}

// Crează fișierul ZIP
// Task to create a ZIP archive using `fs` and `archiver`
function archive(done) {
    setTimeout(() => {
        const output = fs.createWriteStream('./' + pluginName + '.zip'); // Define the ZIP file name
        const archive = archiver('zip', {
            zlib: {level: 9} // Maximum compression level
        });

        // Handle events for the output stream
        output.on('close', () => {
            console.log(`ZIP file created with ${archive.pointer()} total bytes`);
            done();
        });

        archive.on('error', (err) => {
            throw err;
        });

        // Connect the archive stream to the output file stream
        archive.pipe(output);

        // Add the entire `dist` directory to the archive
        archive.directory('dist/', pluginName);

        // Finalize the archive (actually writes the data to the ZIP file)
        archive.finalize();
    }, 100); // Delay de 100ms
}

// Rulează toate task-urile în secvență
const build = gulp.series(
    bumpVersion,
    clean, updateChangelog,
    gulp.parallel(styles, scripts, scriptsSrc, php, libs, copyVersion, copyReadme, copyLanguages),
    images,
    // compressPHP,
    copyVendor,
    addHeader, addReadmeHeader,
    gitTag,
    archive
);

function validateZip(done) {
    const zipPath = `./${pluginName}.zip`;

    if (!fs.existsSync(zipPath)) {
        console.error(`❌ Arhiva ${zipPath} nu există. Asigură-te că task-ul "archive" a rulat.`);
        return done(new Error('Arhiva ZIP nu a fost găsită.'));
    }

    const forbiddenPatterns = [
        /\/vendor\/bin\//,
        /\/vendor\/.*\/test(s)?\//i,
        /\/vendor\/.*\/demo(s)?\//i,
        /\/vendor\/.*\/example(s)?\//i,
        /\/vendor\/.*\/docs\//i,
        /\/tests?\//i,
        /\/node_modules\//,
        /\.md$/i,
        /\.xml$/i,
        /composer\.(json|lock)$/i,
        /gulpfile.*$/i,
        /package(-lock)?\.json$/i
    ];

    const zip = new AdmZip(zipPath);
    const entries = zip.getEntries();

    const forbiddenEntries = entries
        .map(e => e.entryName)
        .filter(name => forbiddenPatterns.some(pattern => pattern.test(name)));

    if (forbiddenEntries.length > 0) {
        console.error('❌ Arhiva conține fișiere sau directoare interzise:');
        forbiddenEntries.forEach(file => console.error(` - ${file}`));
        return done(new Error('Build invalid: arhiva conține fișiere interzise.'));
    }

    console.log('✅ Arhiva este validă. Nu s-au găsit fișiere interzise.');
    done();
}

import { execSync } from 'child_process';

function gitTag(done) {
    const version = JSON.parse(fs.readFileSync('version.json', 'utf8'));
    try {
        execSync(`git tag v${version.version}`);
        console.log(`Tagged: v${version.version}`);
    } catch(e) {
        console.log(`Tag v${version.version} există deja, skip.`);
    }
    done();
}
function updateChangelog(done) {
    let commits;
    try {
        const lastTag = execSync('git describe --tags --abbrev=0').toString().trim();
        commits = execSync(`git log ${lastTag}..HEAD --pretty=format:"* %s"`)
            .toString().trim();
    } catch(e) {
        commits = execSync('git log -10 --pretty=format:"* %s"')
            .toString().trim();
    }

    if (!commits) {
        done(new Error('⛔ ABORT BUILD — Nu sunt commits noi față de ultimul tag!'));
        return;
    }

    const newEntry = `= ${globalConfig.newVersion} =\n${commits}\n`;

    let readme = fs.readFileSync('readme.txt', 'utf8');
    readme = readme.replace('== Changelog ==\n', `== Changelog ==\n${newEntry}\n`);
    fs.writeFileSync('readme.txt', readme);

    console.log(`Changelog actualizat pentru v${globalConfig.newVersion}`);
    done();
}

function deploy(done) {
    const versionData = JSON.parse(fs.readFileSync('version.json', 'utf8'));
    const version = versionData.version;  // ← direct din fișier
    const svnPath = paths.svn;
    const distPath = './dist';

    try {
        execSync(`rm -rf "${svnPath}/trunk/*"`);
        execSync(`cp -r ${distPath}/* "${svnPath}/trunk/"`);
        console.log('✔ Copiat dist/ în trunk/');

        const tagPath = `${svnPath}/tags/${version}`;
        try {
            execSync(`svn copy "${svnPath}/trunk/" "${tagPath}"`);
            console.log(`✔ Tag creat: tags/${version}`);
        } catch(e) {
            console.log(`⚠ Tag ${version} există deja, skip.`);
        }

        execSync(`svn add --force "${svnPath}/trunk/"`);
        execSync(`svn add --force "${tagPath}"`);
        console.log('✔ svn add efectuat');

        execSync(`svn commit "${svnPath}" -m "Release v${version}" --username lenotrep`, {
            stdio: 'inherit'
        });
        console.log(`✔ Deploy v${version} finalizat!`);

    } catch(e) {
        done(new Error(`⛔ Deploy eșuat: ${e.message}`));
        return;
    }

    done();
}

// Exportă task-urile
export {
    clean,
    styles, scripts, php, libs, copyVersion, copyReadme, copyLanguages,
    images,
    // compressPHP,
    copyVendor,
    addHeader, addReadmeHeader, updateChangelog,
    gitTag,
    archive,
    validateZip,
    build,
    deploy
};
