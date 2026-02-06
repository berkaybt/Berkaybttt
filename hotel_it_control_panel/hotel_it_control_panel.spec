# -*- mode: python ; coding: utf-8 -*-
from pathlib import Path

project_root = Path(__file__).parent

block_cipher = None


a = Analysis(
    [str(project_root / "app" / "main.py")],
    pathex=[str(project_root)],
    binaries=[],
    datas=[
        (str(project_root / "app" / "config" / "settings.json"), "app/config"),
        (str(project_root / "app" / "config" / "admin_policy.json"), "app/config"),
        (str(project_root / "app" / "config" / "default_settings.json"), "app/config"),
        (str(project_root / "app" / "config" / "default_admin_policy.json"), "app/config"),
        (str(project_root / "app" / "config" / "settings.schema.json"), "app/config"),
        (str(project_root / "app" / "config" / "admin_policy.schema.json"), "app/config"),
    ],
    hiddenimports=[],
    hookspath=[],
    hooksconfig={},
    runtime_hooks=[],
    excludes=[],
    win_no_prefer_redirects=False,
    win_private_assemblies=False,
    cipher=block_cipher,
    noarchive=False,
)

pyz = PYZ(a.pure, a.zipped_data, cipher=block_cipher)

exe = EXE(
    pyz,
    a.scripts,
    a.binaries,
    a.zipfiles,
    a.datas,
    [],
    name="hotel_it_suite",
    debug=False,
    bootloader_ignore_signals=False,
    strip=False,
    upx=True,
    console=False,
    disable_windowed_traceback=False,
    argv_emulation=False,
    target_arch=None,
    codesign_identity=None,
    entitlements_file=None,
    onefile=True,
)
