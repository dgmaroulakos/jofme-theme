"""Build an installable theme ZIP with a stable jofme/ root."""
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
root = Path(__file__).resolve().parents[1]
files = [p for p in root.rglob('*') if p.is_file() and not any(part.startswith('.') for part in p.relative_to(root).parts) and p.suffix in {'.php', '.css', '.js', '.xml', '.txt', '.png', '.jpg', '.jpeg', '.svg', '.webp', '.woff', '.woff2', '.mo', '.po', '.pot'}]
(root / 'dist').mkdir(exist_ok=True)
with ZipFile(root / 'dist/jofme.zip', 'w', ZIP_DEFLATED) as archive:
    for path in sorted(files):
        if path.relative_to(root).parts[0] != 'dist':
            archive.write(path, 'jofme/' + path.relative_to(root).as_posix())
print(f'Packaged {len(files)} theme files: dist/jofme.zip')
