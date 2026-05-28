#!/usr/bin/env python3
"""
ETL: OPC_EDU_2025.pdf → SQL INSERT for planteles table.
Usage:
  python3 backend/etl/carga_planteles.py temp/OPC_EDU_2025.pdf
  → outputs planteles_inserts.sql in temp/

Parses pdftotext -layout output. Deduplicates by CLAVE.
"""
import re, sys, subprocess, os, random

pdf_path = sys.argv[1] if len(sys.argv) > 1 else 'temp/OPC_EDU_2025.pdf'
r = subprocess.run(['pdftotext', '-layout', pdf_path, '-'], capture_output=True, text=True)
if r.returncode != 0:
    print(f"Error: {r.stderr}", file=sys.stderr); sys.exit(1)

lines = r.stdout.split('\n')

INST_MAP = {
    'CONALEP': 'CONALEP', 'DGETI': 'DGETI', 'I.P.N.': 'IPN', 'IPN': 'IPN',
    'UNAM': 'UNAM', 'COLEGIO DE BACHILLERES': 'COLBACH', 'COLBACH': 'COLBACH',
    'IEMS': 'IEMS', 'DGB': 'DGB', 'CECYTEM': 'CECyTEM', 'CBT': 'CBT',
    'COBAEM': 'COBAEM', 'TELEBACHILLERATO': 'Telebachillerato',
}

EDOMEX = {'TOLUCA','ECATEPEC','NEZAHUALCÓYOTL','NEZAHUALCOYOTL','NAUCALPAN',
    'TLALNEPANTLA','ATIZAPÁN','ATIZAPAN','CUAUTITLÁN','CUAUTITLAN',
    'CHIMALHUACÁN','CHIMALHUACAN','TEXCOCO','COACALCO','LA PAZ','NICOLÁS ROMERO'}

MUNICIPIO_COORDS = {
    'ALVARO OBREGON': (19.358, -99.205),
    'ÁLVARO OBREGÓN': (19.358, -99.205),
    'AZCAPOTZALCO': (19.485, -99.185),
    'BENITO JUAREZ': (19.385, -99.140),
    'BENITO JUÁREZ': (19.385, -99.140),
    'COYOACAN': (19.335, -99.160),
    'COYOACÁN': (19.335, -99.160),
    'CUAJIMALPA': (19.360, -99.310),
    'CUAJIMALPA DE MORELOS': (19.360, -99.310),
    'CUAUHTEMOC': (19.435, -99.145),
    'CUAUHTÉMOC': (19.435, -99.145),
    'GUSTAVO A MADERO': (19.490, -99.110),
    'GUSTAVO A. MADERO': (19.490, -99.110),
    'IZTACALCO': (19.385, -99.095),
    'IZTAPALAPA': (19.345, -99.050),
    'LA MAGDALENA CONTRERAS': (19.310, -99.235),
    'MAGDALENA CONTRERAS': (19.310, -99.235),
    'MIGUEL HIDALGO': (19.415, -99.190),
    'MILPA ALTA': (19.190, -99.025),
    'TLAHUAC': (19.275, -99.040),
    'TLÁHUAC': (19.275, -99.040),
    'TLALPAN': (19.280, -99.180),
    'VENUSTIANO CARRANZA': (19.425, -99.105),
    'XOCHIMILCO': (19.255, -99.090),
    'TOLUCA': (19.290, -99.660),
    'ECATEPEC': (19.600, -99.050),
    'ECATEPEC DE MORELOS': (19.600, -99.050),
    'NEZAHUALCOYOTL': (19.400, -99.030),
    'NEZAHUALCÓYOTL': (19.400, -99.030),
    'NAUCALPAN': (19.480, -99.240),
    'NAUCALPAN DE JUAREZ': (19.480, -99.240),
    'NAUCALPAN DE JUÁREZ': (19.480, -99.240),
    'TLALNEPANTLA': (19.530, -99.190),
    'TLALNEPANTLA DE BAZ': (19.530, -99.190),
    'ATIZAPAN': (19.550, -99.240),
    'ATIZAPÁN': (19.550, -99.240),
    'ATIZAPAN DE ZARAGOZA': (19.550, -99.240),
    'ATIZAPÁN DE ZARAGOZA': (19.550, -99.240),
    'CUAUTITLAN': (19.670, -99.180),
    'CUAUTITLÁN': (19.670, -99.180),
    'CUAUTITLAN IZCALLI': (19.650, -99.210),
    'CUAUTITLÁN IZCALLI': (19.650, -99.210),
    'CHIMALHUACAN': (19.420, -98.960),
    'CHIMALHUACÁN': (19.420, -98.960),
    'TEXCOCO': (19.510, -98.880),
    'TEXCOCO DE MORA': (19.510, -98.880),
    'COACALCO': (19.630, -99.090),
    'COACALCO DE BERRIOZABAL': (19.630, -99.090),
    'LA PAZ': (19.360, -98.950),
    'LOS REYES LA PAZ': (19.360, -98.950),
    'NICOLAS ROMERO': (19.620, -99.310),
    'NICOLÁS ROMERO': (19.620, -99.310),
    'CHALCO': (19.260, -98.890),
    'HUIXQUILUCAN': (19.360, -99.340),
    'HUIXQUILUCÁN': (19.360, -99.340),
    'IZTAPALUCA': (19.290, -98.940),
    'METEPEC': (19.250, -99.600),
    'TECAMAC': (19.710, -98.970),
    'TULTITLAN': (19.640, -99.170),
    'TULTITLÁN': (19.640, -99.170),
    'TULTEPEC': (19.680, -99.130),
    'HUEHUETOCA': (19.830, -99.200),
    'ZINACANTEPEC': (19.290, -99.720),
    'LERMA': (19.280, -99.510),
    'TLALMANALCO': (19.190, -98.800),
    'AMECAMECA': (19.120, -98.770),
    'JILOTEPEC': (19.610, -99.540),
    '__default__': (19.400, -99.100),
}

# Columns (0-indexed, approximate from pdftotext -layout):
#   ALCALDIA(1-18), TIPO(29), INSTITUCION(35-39), CLAVE(63-69)
MAIN_RE = re.compile(
    r'^([\wÁÉÍÓÚáéíóúñÑüÜ /.\-]+?)  +(\d)  +([\wÁÉÍÓÚáéíóúñÑüÜ /.\-]{0,}?)  +([A-Z]\d{6})'
)

ADDR_KEYWORDS = ('C.P.', 'Tel.', 'Fax', 'Col.', 'núm.', 'Av.', 'Calle', 'Carretera', 'Prol.', 'Cda.', 'Andador', 'Cerrada', 'Privada', 'Circuito', 'Retorno', 'Pasaje', 'Eje', 'Calzada', 'Diagonal', 'Boulevard', 'Blvd.', 'Periférico')
SPEC_KEYWORDS = ('Técnico en', 'Técnico ', 'Modalidad', 'Profesional')
SKIP_HEADERS = {'DIRECCIÓN GENERAL', 'DEL BACHILLERATO', 'ALCALDÍA', 'MUNICIPIO', 'TIPO', 'CLAVE',
    'NOMBRE DEL PLANTEL', 'ESPECIALIDAD', 'ÁREA', 'DOMICILIO', 'Página'}

def clean(s):
    return re.sub(r'\s+', ' ', s).strip().rstrip(',;. ')

def esc(s):
    return s.replace("'", "''")[:250]

def is_school_fragment(text):
    t = text.strip()
    if not t or len(t) < 4:
        return False
    if t in SKIP_HEADERS:
        return False
    ut = t.upper()
    if any(kw in ut for kw in ['TEL.', 'FAX', '@', 'C.P.', 'COL.']):
        return False
    # Skip lines that are just specializations or modalidades
    if any(t.startswith(kw) for kw in SPEC_KEYWORDS):
        return False
    return True

def is_address_line(text):
    return any(kw in text for kw in ADDR_KEYWORDS)

records = {}
pending_school = []  # school name fragments from non-MAIN_RE lines before data rows
skip_until = -1       # outer loop index consumed by continuation reading

for i in range(len(lines)):
    if i <= skip_until:
        continue
    line = lines[i]
    m = MAIN_RE.match(line)

    if m:
        alcadia = m.group(1).strip().title()
        inst_text = m.group(3).strip()
        clave = m.group(4).strip()

        if clave in records:
            pending_school = []
            continue

        after_raw = line[m.end():]
        # First non-space position in the raw after text (global line position)
        first_content = None
        for pos, ch in enumerate(after_raw):
            if ch != ' ':
                first_content = m.end() + pos
                break

        after = after_raw.strip()
        after_parts = [p.strip() for p in re.split(r'  {3,}', after) if p.strip()]

        subsistema = 'Otras'
        utxt = inst_text.upper()
        for k, v in INST_MAP.items():
            if k in utxt:
                subsistema = v
                break

        # Determine nombre, especialidad, and initial address from after text
        nombre = ''
        especialidad = ''
        addr_parts = []

        if len(after_parts) >= 2:
            nombre = after_parts[0]
            if len(after_parts) >= 3 or not is_address_line(after_parts[1]):
                especialidad = after_parts[1]
                for ap in after_parts[2:]:
                    if ap.strip():
                        addr_parts.append(ap.strip())
            else:
                addr_parts.append(after_parts[1])
                for ap in after_parts[2:]:
                    if ap.strip():
                        addr_parts.append(ap.strip())
        elif len(after_parts) == 1:
            single = after_parts[0]
            if is_address_line(single):
                addr_parts.append(single)
            elif first_content is not None and first_content < 85:
                nombre = single
            elif any(single.startswith(kw) for kw in SPEC_KEYWORDS):
                especialidad = single
            elif pending_school:
                especialidad = single
            elif any(single.startswith(kw) for kw in ['CENTRO DE', 'COLEGIO DE', 'ESCUELA', 'CONALEP', 'CECYT', 'CBTA', 'CBTIS', 'CETIS']):
                nombre = single
            else:
                especialidad = single

        if not nombre and pending_school:
            nombre = ' '.join(pending_school)

        if not nombre or len(nombre) < 4:
            nombre = clave

        if len(nombre) > 120:
            nombre = nombre[:117] + '...'

        # Collect continuation from NEXT lines (address / school name tail)
        cont_school_parts = []
        cont_addr_parts = list(addr_parts)
        j = i + 1
        while j < len(lines):
            nxt = lines[j]
            if MAIN_RE.match(nxt):
                break
            # Stop if a new school heading starts (content in name column < pos 85)
            ns_pos = None
            for cp, ch in enumerate(nxt):
                if ch != ' ':
                    ns_pos = cp
                    break
            if ns_pos is not None:
                if ns_pos < 50:
                    break
                if ns_pos < 85:
                    nxt_stripped = nxt.strip()
                    nxt_parts = [p.strip() for p in re.split(r'  {3,}', nxt_stripped) if p.strip()]
                    if len(nxt_parts) >= 2 and len(nxt_parts[0]) > 25:
                        break
            t = nxt.strip()
            if not t:
                j += 1
                continue
            if any(t.startswith(kw) for kw in ['ALCALDÍA', 'MUNICIPIO', 'TIPO', 'CLAVE',
                'NOMBRE DEL PLANTEL', 'ESPECIALIDAD', 'ÁREA', 'DOMICILIO', 'Página',
                'DIRECCIÓN GENERAL', 'DEL BACHILLERATO']):
                j += 1
                continue
            cont_parts = [p.strip() for p in re.split(r'  {3,}', t) if p.strip()]
            if len(cont_parts) >= 2:
                first = cont_parts[0]
                if is_school_fragment(first) and not is_address_line(first):
                    cont_school_parts.append(first)
                for cp in cont_parts[1:]:
                    if cp.strip():
                        cont_addr_parts.append(cp.strip())
            else:
                cont_addr_parts.append(cont_parts[0])
            j += 1

        skip_until = j - 1

        if cont_school_parts:
            nombre = clean(nombre + ' ' + ' '.join(cont_school_parts))
            if len(nombre) > 120:
                nombre = nombre[:117] + '...'

        # Build full address
        direccion = clean(', '.join(cont_addr_parts))
        direccion = re.sub(r'[\w.]+@[\w.]+\s*,?\s*', '', direccion)
        direccion = re.sub(r'Tel\.?\s*[\d\-]+\s*,?\s*', '', direccion)
        direccion = re.sub(r'Fax\s*[\d\-]+\s*,?\s*', '', direccion)
        direccion = clean(direccion)
        if len(direccion) > 250:
            direccion = direccion[:247] + '...'

        alcadia_upper = alcadia.upper()
        estado = 'Estado de México' if alcadia_upper in EDOMEX else 'Ciudad de México'

        coords = MUNICIPIO_COORDS.get(alcadia_upper, MUNICIPIO_COORDS.get('__default__'))
        if coords:
            rng = random.Random(clave)
            lat = round(coords[0] + rng.uniform(-0.008, 0.008), 6)
            lng = round(coords[1] + rng.uniform(-0.008, 0.008), 6)
        else:
            lat = lng = 'NULL'

        records[clave] = (clave, nombre, especialidad, subsistema, alcadia, estado, direccion, lat, lng)
        pending_school = []

    else:
        # Non-MAIN_RE line — school name fragment ONLY if it has address on right
        t = line.strip()
        if not t:
            continue
        if any(t.startswith(kw) for kw in ['ALCALDÍA', 'MUNICIPIO', 'TIPO', 'CLAVE',
            'NOMBRE DEL PLANTEL', 'ESPECIALIDAD', 'ÁREA', 'DOMICILIO', 'Página',
            'DIRECCIÓN GENERAL', 'DEL BACHILLERATO', 'Técnico en', 'Técnico ']):
            continue
        if '@' in t:
            continue
        if t in SKIP_HEADERS:
            continue
        parts = [p.strip() for p in re.split(r'  {3,}', t) if p.strip()]
        if len(parts) >= 2:
            first = parts[0]
            if is_school_fragment(first) and not is_address_line(first):
                pending_school.append(first)

# Generate SQL
out_path = os.path.join(os.path.dirname(pdf_path) if os.path.dirname(pdf_path) else '.', 'planteles_inserts.sql')
with open(out_path, 'w', encoding='utf-8') as f:
    f.write(f"-- Generated by carga_planteles.py from {os.path.basename(pdf_path)}\n")
    f.write(f"-- {len(records)} unique planteles loaded\n\n")
    f.write("USE ecoems_db;\n\n")
    f.write("TRUNCATE TABLE planteles;\n\n")
    f.write("INSERT INTO planteles (clave, nombre, especialidad, subsistema, municipio, estado, direccion, latitud, longitud) VALUES\n")

    batch = list(records.values())
    for idx, rec in enumerate(batch):
        clave, nombre, especialidad, subsistema, alcadia, estado, direccion, lat, lng = rec
        lat_str = f"'{lat}'" if lat != 'NULL' else 'NULL'
        lng_str = f"'{lng}'" if lng != 'NULL' else 'NULL'
        comma = ',' if idx < len(batch) - 1 else ';'
        f.write(f"('{esc(clave)}','{esc(nombre)}','{esc(especialidad)}','{esc(subsistema)}','{esc(alcadia)}','{esc(estado)}','{esc(direccion)}',{lat_str},{lng_str}){comma}\n")

print(f"✅ {len(records)} planteles → {out_path}")
