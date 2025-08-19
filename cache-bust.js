// Cache busting utility for production builds
// This script can be used to add timestamps to asset URLs

import fs from 'fs';
import path from 'path';

// Generate a cache-busting timestamp
const timestamp = Date.now();

// Function to add cache-busting parameter to URLs
function addCacheBust(url) {
    const separator = url.includes('?') ? '&' : '?';
    return `${url}${separator}v=${timestamp}`;
}

// Function to update asset references in built files
function updateAssetReferences(buildDir) {
    const files = fs.readdirSync(buildDir, { recursive: true });
    
    files.forEach(file => {
        if (file.endsWith('.html') || file.endsWith('.js') || file.endsWith('.css')) {
            const filePath = path.join(buildDir, file);
            let content = fs.readFileSync(filePath, 'utf8');
            
            // Update asset references with cache-busting parameters
            content = content.replace(
                /(href|src)="([^"]+\.(css|js|png|jpg|jpeg|gif|svg|ico))"/g,
                (match, attr, url, ext) => {
                    if (!url.includes('http') && !url.includes('v=')) {
                        return `${attr}="${addCacheBust(url)}"`;
                    }
                    return match;
                }
            );
            
            fs.writeFileSync(filePath, content);
        }
    });
}

// Export for use in build scripts
export {
    addCacheBust,
    updateAssetReferences,
    timestamp
};

// CLI usage
if (import.meta.url === `file://${process.argv[1]}`) {
    const buildDir = process.argv[2] || './public/build';
    if (fs.existsSync(buildDir)) {
        updateAssetReferences(buildDir);
        console.log(`Cache busting applied to ${buildDir} with timestamp: ${timestamp}`);
    } else {
        console.error(`Build directory not found: ${buildDir}`);
    }
}