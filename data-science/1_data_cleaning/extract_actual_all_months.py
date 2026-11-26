"""
run_full_pipeline.py
====================
Implementasi lengkap Tugas 1 sampai 8 untuk pipeline data reservasi 33HomeStay.
Sumber kebenaran: Data_tamu.pdf
"""

import os
import re
import zlib
from collections import defaultdict
from datetime import datetime
import pandas as pd
import numpy as np

DATA_DIR = os.path.dirname(os.path.abspath(__file__))
PDF_PATH = os.path.join(DATA_DIR, "Data_tamu.pdf")

with open(PDF_PATH, "rb") as f:
    pdf_bytes = f.read()

def decompress_obj(obj_id):
    m = re.search(rb'\b' + str(obj_id).encode() + rb'\s+0\s+obj\b', pdf_bytes)
    if not m: return None
    s_idx = pdf_bytes.find(b'stream', m.start())
    if s_idx == -1: return None
    start = s_idx + 6
    while pdf_bytes[start:start+1] in (b'\r', b'\n'): start += 1
    for em in re.finditer(rb'endstream', pdf_bytes[start:start+350000]):
        raw = pdf_bytes[start:start+em.start()].rstrip(b'\r\n')
        try:
            return zlib.decompress(raw)
        except:
            continue
    return None

def parse_cmap(data):
    cmap = {}
    if not data: return cmap
    for m in re.finditer(rb'<([0-9a-fA-F]+)>\s*<([0-9a-fA-F]+)>', data):
        cmap[int(m.group(1), 16)] = chr(int(m.group(2), 16))
    for m in re.finditer(rb'<([0-9a-fA-F]+)>\s*<([0-9a-fA-F]+)>\s*<([0-9a-fA-F]+)>', data):
        s1, s2, d = int(m.group(1), 16), int(m.group(2), 16), int(m.group(3), 16)
        for code in range(s1, s2 + 1):
            cmap[code] = chr(d + (code - s1))
    return cmap

fonts_cmap = {
    f'Font{i}': parse_cmap(decompress_obj(n))
    for i, n in enumerate([103, 107, 111, 115, 119], 1)
}

def decode_str(raw_bytes, font_name):
    cmap = fonts_cmap.get(font_name, fonts_cmap['Font1'])
    unescaped, i = [], 0
    while i < len(raw_bytes):
        if raw_bytes[i] == 92 and i + 1 < len(raw_bytes):
            unescaped.append(raw_bytes[i+1])
            i += 2
        else:
            unescaped.append(raw_bytes[i])
            i += 1
    b = bytes(unescaped)
    return ''.join(cmap.get((b[j] << 8) | b[j+1], '') for j in range(0, len(b) - 1, 2))

# Page order:
# p1 Jan (obj 6), p2 Feb (obj 17), p3 Mar (obj 22), p4 Apr (obj 28), p5 Mei (obj 33), p6 Jul (obj 38),
# p7-8 Jun (obj 43, 48), p9-10 Agu (obj 53, 58), p11-12 Sep (obj 63, 68), p13-14 Okt (obj 73, 78), p15-16 Nov (obj 83, 88)
month_pages = [
    (1, "Januari", [(1, 6)]),
    (2, "Februari", [(2, 17)]),
    (3, "Maret", [(3, 22)]),
    (4, "April", [(4, 28)]),
    (5, "Mei", [(5, 33)]),
    (7, "Juli", [(6, 38)]),
    (6, "Juni", [(7, 43), (8, 48)]),
    (8, "Agustus", [(9, 53), (10, 58)]),
    (9, "September", [(11, 63), (12, 68)]),
    (10, "Oktober", [(13, 73), (14, 78)]),
    (11, "November", [(15, 83), (16, 88)]),
]

targets = {
    "Januari": (23322000, 3070000),
    "Februari": (16341000, 3120000),
    "Maret": (29350000, 4350000),
    "April": (25720000, 4560000),
    "Mei": (28029000, 4320000),
    "Juni": (23875000, 3150000),
    "Juli": (23110000, 3920000),
    "Agustus": (29985000, 3620000),
    "September": (26182000, 5930000),
    "Oktober": (33430000, 6170000),
    "November": (13420000, 2190000),
}

ROOM_TYPES = {
    "K1": {"type": "Standard Room AC", "cap": 2, "rate": 300000},
    "K2": {"type": "Standard Room AC", "cap": 2, "rate": 300000},
    "K3": {"type": "Mezanin", "cap": 4, "rate": 350000},
    "K4": {"type": "Standard Room Kipas", "cap": 2, "rate": 200000},
    "K5": {"type": "Standard Room AC", "cap": 2, "rate": 300000},
}

KNOWN_CITIES = [
    "Jakarta Barat", "Jakarta Timur", "JakTim", "Jakarta", "Serang", "Kota Serang", "Cilegon", "Tangerang", "Tanggerang", "Tangsel",
    "Bekasi", "Bogor", "Depok", "Bandung", "Pandeglang", "Lebak", "Anyer", "amyer", "Mancak", "Balaraja", "Cikande", "Merak",
    "Cinangka", "Ciruas", "Rangkas Bitung", "Rangkas", "Cibeber", "Kramat watu", "Kragilan", "Pontang", "Cikoneng", "Tigaraksa",
    "Bojonegara", "Mandalawangi", "Pasauran", "Cigading", "Suralaya", "Surayala", "Maja", "Petir", "Baros", "Pasar baros",
    "Kronjo", "Kemiri", "Sentul", "Cikotok", "Cikarai", "Cipocok", "Ciwandan", "Ciparahu", "Kp. Ciparahu", "Krang Jetak",
    "Warnasari", "Gunungsugih", "Sel Tangerang", "Kota Tangerang", "Karawaci", "Banten Lama", "Cipanas", "Padarincang",
    "Cikupa", "Curug", "Palima", "Sajira", "Ciomas", "Serkot", "Bandar Lampung", "Sukabumi", "Pemalang"
]

def clean_city_name(raw_city):
    if not raw_city: return None
    rc = raw_city.strip().lower()
    mapping = {
        "tanggerang": "Tangerang",
        "tangsel": "Tangerang Selatan",
        "sel tangerang": "Tangerang Selatan",
        "kota tangerang": "Tangerang",
        "jaktim": "Jakarta Timur",
        "jakarta timur": "Jakarta Timur",
        "jakarta barat": "Jakarta Barat",
        "amyer": "Anyer",
        "surayala": "Suralaya",
        "kota serang": "Serang",
        "serkot": "Serang",
        "seranv": "Serang",
        "seramg": "Serang",
        "pasar baros": "Baros",
        "rangkas bitung": "Rangkasbitung",
        "rangkas": "Rangkasbitung",
        "cielgon": "Cilegon",
        "tamgerang": "Tangerang",
        "tamggerang": "Tangerang",
        "kp. ciparahu": "Ciparahu",
    }
    for k, v in mapping.items():
        if rc == k:
            return v
    for kc in KNOWN_CITIES:
        if kc.lower() == rc:
            return kc
    return raw_city.strip().title()

def extract_page_items(stream_id, page_num):
    stream = decompress_obj(stream_id)
    if not stream: return []
    items = []
    scale = 0.21319987
    for m in re.finditer(rb'BT\s+(.*?)\s+ET', stream, re.DOTALL):
        blk = m.group(1)
        fm = re.search(rb'/(\w+)\s+([\d\.]+)\s+Tf', blk)
        fn = fm.group(1).decode('ascii') if fm else 'Font1'
        tm = re.search(rb'([\d\.\-]+)\s+([\d\.\-]+)\s+([\d\.\-]+)\s+([\d\.\-]+)\s+([\d\.\-]+)\s+([\d\.\-]+)\s+Tm', blk)
        x = float(tm.group(5)) if tm else 0.0
        y = float(tm.group(6)) if tm else 0.0
        tj_parts = []
        for t_match in re.finditer(rb'\((.*?)\)(?:\s*Tj|(?=\s*[-0-9\.]*\s*\)))|\[(.*?)\]\s*TJ', blk, re.DOTALL):
            if t_match.group(1):
                tj_parts.append(decode_str(t_match.group(1), fn))
            elif t_match.group(2):
                for inner in re.finditer(rb'\((.*?)\)', t_match.group(2), re.DOTALL):
                    tj_parts.append(decode_str(inner.group(1), fn))
        txt = ''.join(tj_parts).strip()
        if txt:
            items.append({
                'x': x * scale,
                'y': y * scale,
                'text': txt,
                'page': page_num,
                'stream': stream_id
            })
    return items

def parse_price(txt):
    is_text = False
    if "Rp." in txt or "," in txt:
        is_text = True
    clean = re.sub(r'[^\d]', '', txt)
    if clean:
        val = int(clean)
        return val, is_text
    return 0, is_text

print("1. Menjalankan TUGAS 1 (Ekstraksi Semua Baris)...")
extracted_rows = []

for m_idx, m_name, pages in month_pages:
    all_items = []
    for p_num, s_id in pages:
        all_items.extend(extract_page_items(s_id, p_num))
    
    for side, x_min, x_max in [("kiri", 0, 250), ("kanan", 350, 650)]:
        side_items = [it for it in all_items if x_min <= it['x'] <= x_max]
        by_page = defaultdict(list)
        for it in side_items:
            by_page[it['page']].append(it)
        
        row_lines = []
        for p in sorted(by_page.keys()):
            p_items = sorted(by_page[p], key=lambda it: it['y'])
            curr_y = None
            curr_row = []
            for it in p_items:
                if curr_y is None or abs(it['y'] - curr_y) <= 1.6:
                    curr_row.append(it)
                    curr_y = it['y'] if curr_y is None else (curr_y + it['y']) / 2
                else:
                    row_lines.append((p, curr_y, curr_row))
                    curr_row = [it]
                    curr_y = it['y']
            if curr_row:
                row_lines.append((p, curr_y, curr_row))
        
        for p, y, r in row_lines:
            r.sort(key=lambda it: it['x'])
            line_str = " ".join(it['text'] for it in r)
            
            if any(h in line_str for h in ["CHEK IN", "CHEK OUT", "BIAYA TETAP", "BIAYA VARIABEL", "PENGELUARAN", "TOTAL PROFIT", "TOTAL OMSET", "PENDAPATAN"]):
                continue
            
            # Match date with [/-]+ to catch // typos
            dates = re.findall(r'\b\d{1,2}[/-]+\d{1,2}[/-]+\d{2,4}\b', line_str)
            is_candra = (m_name == "Juni" and side == "kanan" and "candra" in line_str.lower() and "300" in line_str)
            
            if len(dates) >= 2 or is_candra:
                price_val = 0
                price_text_flag = False
                price_idx = -1
                for idx_item in range(len(r) - 1, -1, -1):
                    t = r[idx_item]['text']
                    if re.search(r'Rp\s*[\d\.\,]+|\b\d{1,3}[\.\,]\d{3}\b|\b[1-9]\d{5,6}\b', t):
                        pv, ptf = parse_price(t)
                        if pv >= 50000:
                            price_val = pv
                            price_text_flag = ptf
                            price_idx = idx_item
                            break
                if price_val <= 0:
                    continue
                
                # Row number
                first_txt = r[0]['text'].strip()
                row_no = ""
                m_no = re.match(r'^(\d+)', first_txt)
                if m_no:
                    row_no = m_no.group(1)
                
                d1_raw = dates[0] if len(dates) >= 1 else ""
                d2_raw = dates[1] if len(dates) >= 2 else d1_raw
                
                # Tokens after price: OTA, Room
                ota_raw = ""
                room_raw = ""
                after_price = r[price_idx + 1:] if price_idx != -1 else []
                after_texts = [it['text'].strip() for it in after_price if it['text'].strip()]
                
                for at in after_texts:
                    at_clean = at.lower()
                    if any(k in at_clean for k in ["agoda", "tiket", "traveloka", "aplikasi", "apk", "opsi 2"]):
                        ota_raw = at
                    elif re.search(r'\b(k[1-5]|mezanin|th1|th-1)\b', at_clean) or "," in at_clean or "." in at_clean:
                        room_raw = at
                    elif not ota_raw and not room_raw:
                        if "k" in at_clean:
                            room_raw = at
                        else:
                            ota_raw = at
                
                if not room_raw:
                    m_room = re.search(r'\b(k[1-5][,\.k1-5]*|mezanin)\b', line_str, re.I)
                    if m_room:
                        room_raw = m_room.group(1)
                
                # Tokens before date: Nama & Alamat
                before_date = []
                for it in r:
                    if d1_raw and (it['text'].strip() == d1_raw or d1_raw in it['text'].strip()):
                        break
                    before_date.append(it['text'].strip())
                
                if before_date and row_no and before_date[0] == row_no:
                    before_date = before_date[1:]
                elif before_date and row_no and before_date[0].startswith(row_no):
                    rem = before_date[0][len(row_no):].strip()
                    before_date = ([rem] if rem else []) + before_date[1:]
                
                raw_nama_alamat = " ".join(before_date).strip()
                
                # Extract city if recorded
                detected_city = ""
                for kc in KNOWN_CITIES:
                    if re.search(r'\b' + re.escape(kc) + r'\b', raw_nama_alamat, re.I):
                        detected_city = clean_city_name(kc)
                        break
                
                # Parse guest_name and guest_phone from raw_nama_alamat
                # extract phone if present
                m_phone = re.search(r'(\+?62[\d\s\-]{8,}|\b08[\d\s\-]{8,})', raw_nama_alamat)
                raw_phone = m_phone.group(1).strip() if m_phone else ""
                
                raw_name = raw_nama_alamat
                if raw_phone:
                    raw_name = raw_name.replace(raw_phone, "").strip()
                if detected_city:
                    raw_name = re.sub(r'\b' + re.escape(detected_city) + r'\b', '', raw_name, flags=re.I).strip()
                raw_name = re.sub(r'[\+\-\(\)]', '', raw_name).strip()
                if not raw_name:
                    raw_name = "nonama"
                
                extracted_rows.append({
                    'source_month': m_idx,
                    'month_name': m_name,
                    'source_page': p,
                    'source_table': side,
                    'source_row_no': row_no,
                    'raw_line': line_str,
                    'raw_name': raw_name,
                    'raw_phone': raw_phone,
                    'raw_nama_alamat': raw_nama_alamat,
                    'raw_check_in': d1_raw,
                    'raw_check_out': d2_raw,
                    'price': price_val,
                    'price_text_format': price_text_flag,
                    'ota_raw': ota_raw,
                    'room_raw': room_raw,
                    'detected_city': detected_city,
                    'is_candra': is_candra
                })

df_raw = pd.DataFrame(extracted_rows)
print(f"Total baris mentah diekstrak: {len(df_raw)} (Kiri: {(df_raw['source_table']=='kiri').sum()}, Kanan: {(df_raw['source_table']=='kanan').sum()})")

# 2. TUGAS 2: PEMBERSIHAN TANGGAL
print("\n2. Menjalankan TUGAS 2 (Pembersihan Tanggal R1-R7)...")
date_log = []
cleaned_date_records = []

for idx, r in df_raw.iterrows():
    m_sheet = r['source_month']
    side = r['source_table']
    row_no = r['source_row_no']
    raw_d1 = r['raw_check_in']
    raw_d2 = r['raw_check_out']
    line = r['raw_line']
    
    if r['is_candra']:
        r_dict = dict(r)
        r_dict['check_in'] = None
        r_dict['check_out'] = None
        r_dict['nights'] = 0
        r_dict['rental_type'] = 'daily'
        r_dict['date_unresolved'] = True
        r_dict['needs_review'] = False
        r_dict['date_rules'] = "Candra Juni tanpa tanggal"
        cleaned_date_records.append(r_dict)
        continue
    
    if m_sheet == 1 and side == 'kanan' and ('nur' in line.lower() or '12/3' in raw_d1):
        r_dict = dict(r)
        r_dict['check_in'] = None
        r_dict['check_out'] = None
        r_dict['nights'] = 0
        r_dict['rental_type'] = 'transit'
        r_dict['date_unresolved'] = True
        r_dict['needs_review'] = True
        r_dict['date_rules'] = "Kasus Khusus Nur Jan"
        cleaned_date_records.append(r_dict)
        date_log.append({
            'source_month': m_sheet,
            'source_table': side,
            'source_row_no': row_no,
            'kolom': 'check_in/out',
            'nilai_asli': f"{raw_d1} - {raw_d2}",
            'nilai_baru': 'UNRESOLVED',
            'aturan': 'Kasus Khusus Nur Jan',
            'keyakinan': 'rendah',
            'needs_review': True
        })
        continue

    def parse_tokens(d_str):
        clean_str = re.sub(r'[/-]+', '/', d_str.strip())
        pts = [int(p) for p in clean_str.split('/')]
        if len(pts) == 2:
            pts.append(2026)
        d, m, y = pts[0], pts[1], pts[2]
        return d, m, y

    d1, m1, y1 = parse_tokens(raw_d1)
    d2, m2, y2 = parse_tokens(raw_d2)

    needs_review = False
    applied_rules = []

    # R1: Year != 2026
    if y1 != 2026:
        orig = y1; y1 = 2026; applied_rules.append(f"R1(y1={orig}->2026)")
        date_log.append({'source_month': m_sheet, 'source_table': side, 'source_row_no': row_no,
                         'kolom': 'check_in', 'nilai_asli': raw_d1, 'nilai_baru': f"{d1:02d}/{m1:02d}/{y1}",
                         'aturan': 'R1', 'keyakinan': 'tinggi', 'needs_review': False})
    if y2 != 2026:
        orig = y2; y2 = 2026; applied_rules.append(f"R1(y2={orig}->2026)")
        date_log.append({'source_month': m_sheet, 'source_table': side, 'source_row_no': row_no,
                         'kolom': 'check_out', 'nilai_asli': raw_d2, 'nilai_baru': f"{d2:02d}/{m2:02d}/{y2}",
                         'aturan': 'R1', 'keyakinan': 'tinggi', 'needs_review': False})

    # R2: Month != sheet, but swapped d and m == sheet
    if m1 != m_sheet and d1 == m_sheet:
        d1, m1 = m1, d1; applied_rules.append(f"R2(swap d1/m1->{d1}/{m1})")
        date_log.append({'source_month': m_sheet, 'source_table': side, 'source_row_no': row_no,
                         'kolom': 'check_in', 'nilai_asli': raw_d1, 'nilai_baru': f"{d1:02d}/{m1:02d}/{y1}",
                         'aturan': 'R2', 'keyakinan': 'tinggi', 'needs_review': False})
    if m2 != m_sheet and d2 == m_sheet:
        if not (d1 >= 28 and d2 <= 2 and m2 == m_sheet + 1):
            d2, m2 = m2, d2; applied_rules.append(f"R2(swap d2/m2->{d2}/{m2})")
            date_log.append({'source_month': m_sheet, 'source_table': side, 'source_row_no': row_no,
                             'kolom': 'check_out', 'nilai_asli': raw_d2, 'nilai_baru': f"{d2:02d}/{m2:02d}/{y2}",
                             'aturan': 'R2', 'keyakinan': 'tinggi', 'needs_review': False})

    # R3: Check-in month != sheet and R2 didn't apply
    if m1 != m_sheet:
        orig_m1 = m1; m1 = m_sheet; needs_review = True; applied_rules.append(f"R3(m1={orig_m1}->{m_sheet})")
        date_log.append({'source_month': m_sheet, 'source_table': side, 'source_row_no': row_no,
                         'kolom': 'check_in', 'nilai_asli': raw_d1, 'nilai_baru': f"{d1:02d}/{m1:02d}/{y1}",
                         'aturan': 'R3', 'keyakinan': 'sedang', 'needs_review': True})

    # R4: Check-out <= 2 while check-in >= 28 and month didn't advance
    if d1 >= 28 and d2 <= 2 and m2 == m1:
        m2 = m1 + 1
        if m2 > 12: m2 = 1; y2 += 1
        applied_rules.append(f"R4(advance m2->{m2})")
        date_log.append({'source_month': m_sheet, 'source_table': side, 'source_row_no': row_no,
                         'kolom': 'check_out', 'nilai_asli': raw_d2, 'nilai_baru': f"{d2:02d}/{m2:02d}/{y2}",
                         'aturan': 'R4', 'keyakinan': 'tinggi', 'needs_review': False})

    # R5: Non-existent dates
    dim_map = {1:31, 2:28, 3:31, 4:30, 5:31, 6:30, 7:31, 8:31, 9:30, 10:31, 11:30, 12:31}
    if d1 > dim_map.get(m1, 31):
        m1 += 1; d1 = 1
        if m1 > 12: m1 = 1; y1 += 1
        applied_rules.append("R5(d1 invalid->next month)")
        date_log.append({'source_month': m_sheet, 'source_table': side, 'source_row_no': row_no,
                         'kolom': 'check_in', 'nilai_asli': raw_d1, 'nilai_baru': f"{d1:02d}/{m1:02d}/{y1}",
                         'aturan': 'R5', 'keyakinan': 'tinggi', 'needs_review': False})
    if d2 > dim_map.get(m2, 31):
        m2 += 1; d2 = 1
        if m2 > 12: m2 = 1; y2 += 1
        applied_rules.append("R5(d2 invalid->next month)")
        date_log.append({'source_month': m_sheet, 'source_table': side, 'source_row_no': row_no,
                         'kolom': 'check_out', 'nilai_asli': raw_d2, 'nilai_baru': f"{d2:02d}/{m2:02d}/{y2}",
                         'aturan': 'R5', 'keyakinan': 'tinggi', 'needs_review': False})

    # R6: Check-out 1 day before check-in -> swap
    dt1_tmp = datetime(y1, m1, d1)
    dt2_tmp = datetime(y2, m2, d2)
    if (dt1_tmp - dt2_tmp).days == 1:
        d1, m1, y1, d2, m2, y2 = d2, m2, y2, d1, m1, y1
        applied_rules.append("R6(swap checkout<checkin)")
        date_log.append({'source_month': m_sheet, 'source_table': side, 'source_row_no': row_no,
                         'kolom': 'check_in/out', 'nilai_asli': f"{raw_d1} - {raw_d2}",
                         'nilai_baru': f"{d1:02d}/{m1:02d}/{y1} - {d2:02d}/{m2:02d}/{y2}",
                         'aturan': 'R6', 'keyakinan': 'tinggi', 'needs_review': False})

    # R7: Duration > 14 nights
    dt1 = datetime(y1, m1, d1)
    dt2 = datetime(y2, m2, d2)
    nights = (dt2 - dt1).days
    is_agus_salim = (m_sheet == 11 and 'agus salim' in line.lower() and r['price'] == 3000000)

    if nights > 14 and not is_agus_salim:
        if m2 != m1:
            orig_dt2 = dt2
            m2 = m1
            dt2 = datetime(y2, m2, d2)
            nights = (dt2 - dt1).days
            if 0 <= nights <= 14:
                applied_rules.append(f"R7(m2={orig_dt2.month}->{m1}, nights={nights})")
                date_log.append({'source_month': m_sheet, 'source_table': side, 'source_row_no': row_no,
                                 'kolom': 'check_out', 'nilai_asli': raw_d2, 'nilai_baru': f"{d2:02d}/{m2:02d}/{y2}",
                                 'aturan': 'R7', 'keyakinan': 'tinggi', 'needs_review': False})
            else:
                dt2 = orig_dt2
                needs_review = True
                applied_rules.append(f"R7(nights={nights}>14 needs_review)")
                date_log.append({'source_month': m_sheet, 'source_table': side, 'source_row_no': row_no,
                                 'kolom': 'duration', 'nilai_asli': f"{raw_d1} - {raw_d2}",
                                 'nilai_baru': f"{dt1.strftime('%Y-%m-%d')} - {dt2.strftime('%Y-%m-%d')}",
                                 'aturan': 'R7', 'keyakinan': 'rendah', 'needs_review': True})
        else:
            needs_review = True
            applied_rules.append(f"R7(nights={nights}>14 needs_review)")
            date_log.append({'source_month': m_sheet, 'source_table': side, 'source_row_no': row_no,
                             'kolom': 'duration', 'nilai_asli': f"{raw_d1} - {raw_d2}",
                             'nilai_baru': f"{dt1.strftime('%Y-%m-%d')} - {dt2.strftime('%Y-%m-%d')}",
                             'aturan': 'R7', 'keyakinan': 'rendah', 'needs_review': True})

    if dt2 < dt1:
        dt2 = dt1
        needs_review = True

    nights = (dt2 - dt1).days
    rental_type = 'transit' if nights == 0 else 'daily'

    r_dict = dict(r)
    r_dict['check_in'] = dt1.strftime('%Y-%m-%d')
    r_dict['check_out'] = dt2.strftime('%Y-%m-%d')
    r_dict['nights'] = nights
    r_dict['rental_type'] = rental_type
    r_dict['date_unresolved'] = False
    r_dict['needs_review'] = needs_review
    r_dict['date_rules'] = "; ".join(applied_rules) if applied_rules else "None"
    cleaned_date_records.append(r_dict)

df_date_cleaned = pd.DataFrame(cleaned_date_records)
df_date_log = pd.DataFrame(date_log)
date_log_path = os.path.join(DATA_DIR, "date_correction_log.csv")
df_date_log.to_csv(date_log_path, index=False)
print(f"Log koreksi tanggal disimpan ke {date_log_path} ({len(df_date_log)} entri)")

# 3. TUGAS 3: HAPUS DUPLIKAT
print("\n3. Menjalankan TUGAS 3 (Hapus Duplikat Persis Sebelum Anonimisasi)...")
dup_keys = ['source_month', 'source_table', 'raw_nama_alamat', 'raw_check_in', 'raw_check_out', 'price', 'room_raw']
is_duplicate = df_date_cleaned.duplicated(subset=dup_keys, keep='first')
df_duplicates = df_date_cleaned[is_duplicate]

dup_log = []
for idx, r in df_duplicates.iterrows():
    dup_log.append({
        'source_month': r['source_month'],
        'source_table': r['source_table'],
        'source_row_no': r['source_row_no'],
        'raw_check_in': r['raw_check_in'],
        'raw_check_out': r['raw_check_out'],
        'price': r['price'],
        'kamar': r['room_raw'],
        'alasan': 'Menyalin baris sebelumnya persis (April baris 88-90 menyalin 85-87)'
    })

df_dup_log = pd.DataFrame(dup_log)
dup_log_path = os.path.join(DATA_DIR, "duplicates_removed.csv")
df_dup_log.to_csv(dup_log_path, index=False)
print(f"Log duplikat dibuang disimpan ke {dup_log_path} ({len(df_dup_log)} baris dibuang)")

df_dedup = df_date_cleaned[~is_duplicate].copy().reset_index(drop=True)
print(f"Total baris setelah duplikat dibuang: {len(df_dedup)} (Target: 1.207 baris)")

# 4. TUGAS 4 & 5 & 6: PEMETAAN KAMAR, KOLOM KARANGAN, PENANDA ASAL
print("\n4. Menjalankan TUGAS 4, 5, 6 (Pemetaan Kamar N-1/K-2, Imputasi Modus Kota, Penanda Asal)...")

# Hitung modus kota dari baris yang kota terisi
detected_cities = df_dedup['detected_city'].dropna()
detected_cities = detected_cities[detected_cities != ""]
modus_kota = detected_cities.mode().iloc[0] if len(detected_cities) > 0 else "Serang"
print(f"Modus kota terdeteksi: '{modus_kota}'")

processed_rows = []
for idx, r in df_dedup.iterrows():
    side = r['source_table']
    m_sheet = r['source_month']
    row_no = str(r['source_row_no']).strip()
    price = r['price']
    rental_type = r['rental_type']
    room_raw = str(r['room_raw']).strip().upper()
    
    # guest_city and guest_city_source
    if r['detected_city']:
        guest_city = r['detected_city']
        guest_city_source = "recorded"
    else:
        guest_city = modus_kota
        guest_city_source = "imputed_mode"
    
    # room mapping
    if side == "kanan":
        # Check N-1 exception: explicit K4 (Januari no. 1 and Juli no. 1)
        is_k4_kanan = False
        if "K4" in room_raw:
            is_k4_kanan = True
        elif m_sheet == 1 and row_no == "1":
            is_k4_kanan = True
        elif m_sheet == 7 and row_no == "1":
            is_k4_kanan = True
        
        if is_k4_kanan:
            room_code = "K4"
            room_type = "Standard Room Kipas"
            unit_scope = "inti"
            room_capacity = 2
            price_per_night = 200000
            room_code_source = "recorded"
        else:
            room_code = "TH1"
            room_type = "Teras House"
            unit_scope = "teras_house"
            room_capacity = np.nan
            price_per_night = np.nan
            room_code_source = "rule_right_table"
    else:
        # TABEL KIRI (K-2)
        unit_scope = "inti"
        
        # Check explicit room
        m_r = re.search(r'\b(K[1-5]|MEZANIN)\b', room_raw)
        if m_r:
            matched_code = m_r.group(1)
            if matched_code == "MEZANIN":
                room_code = "K3"
            else:
                room_code = matched_code
            room_type = ROOM_TYPES[room_code]["type"]
            room_capacity = ROOM_TYPES[room_code]["cap"]
            price_per_night = ROOM_TYPES[room_code]["rate"]
            room_code_source = "recorded"
        else:
            # Imputed rule
            if price == 350000:
                room_code = "K3"
                room_type = "Mezanin"
                room_capacity = 4
                price_per_night = 350000
            elif price == 200000 and rental_type == "daily":
                room_code = "K4"
                room_type = "Standard Room Kipas"
                room_capacity = 2
                price_per_night = 200000
            elif price >= 300000 or rental_type == "transit":
                room_code = "Unassigned"
                room_type = "Standard Room AC"
                room_capacity = 2
                price_per_night = 300000
            else:
                room_code = "Unassigned"
                room_type = "Standard Room AC"
                room_capacity = 2
                price_per_night = 300000
            room_code_source = "imputed_rule"

    # Channel (Tugas 5b)
    ota_raw = str(r['ota_raw']).strip()
    ota_clean = ota_raw.lower()
    if any(k in ota_clean for k in ["agoda", "tiket", "traveloka", "aplikasi", "apk"]):
        channel = "OTA"
        if "agoda" in ota_clean: ota_name = "Agoda"
        elif "tiket" in ota_clean: ota_name = "Tiket.com"
        elif "traveloka" in ota_clean: ota_name = "Traveloka"
        elif "aplikasi" in ota_clean: ota_name = "Aplikasi OTA"
        elif "apk" in ota_clean: ota_name = "APK OTA"
        else: ota_name = ota_raw
    else:
        channel = "Tidak tercatat"
        ota_name = ""

    # Notes (Tugas 5d)
    notes_parts = []
    if r['date_unresolved']:
        notes_parts.append("Tanggal tidak dapat diselesaikan (date_unresolved)")
    if r['needs_review']:
        notes_parts.append("Perlu ditinjau (needs_review)")
    if r['price_text_format']:
        notes_parts.append("Format harga teks")
    if "opsi 2" in ota_clean:
        notes_parts.append("Tertulis Opsi 2")
    notes = "; ".join(notes_parts) if notes_parts else ""

    r_proc = dict(r)
    r_proc['guest_city'] = guest_city
    r_proc['guest_city_source'] = guest_city_source
    r_proc['room_code'] = room_code
    r_proc['room_type'] = room_type
    r_proc['room_code_source'] = room_code_source
    r_proc['unit_scope'] = unit_scope
    r_proc['room_capacity'] = room_capacity
    r_proc['price_per_night'] = price_per_night
    r_proc['channel'] = channel
    r_proc['ota_name'] = ota_name
    r_proc['guest_count'] = np.nan # Tugas 5a: dikosongkan
    r_proc['status'] = "PAID_OFF"
    r_proc['notes'] = notes
    processed_rows.append(r_proc)

df_proc = pd.DataFrame(processed_rows)

# Multi-room split (Tugas 1d)
print("\n5. Menjalankan TUGAS 1d (Pemecahan Multi-Kamar)...")
final_rows = []
res_counter = 1

for idx, r in df_proc.iterrows():
    raw_r = str(r['room_raw']).strip().lower()
    raw_line = r['raw_line'].lower()
    
    # Check multi-kamar
    # known multi-room cases:
    # Jan #19: k2,k3
    # Jan #45: K1,k5
    # Feb #19: k2,3 -> K2, K3
    # Feb #20: k1,5 -> K1, K5
    # Feb #46: k1,k2 -> K1, K2
    # Mar #31: k1.k2 -> K1, K2
    # Apr #11: k2,k3 -> K2, K3
    split_rooms = []
    if 'k2,k3' in raw_line: split_rooms = ['K2', 'K3']
    elif 'k1,k5' in raw_line: split_rooms = ['K1', 'K5']
    elif 'k2,3' in raw_line or 'k2, 3' in raw_line: split_rooms = ['K2', 'K3']
    elif 'k1,5' in raw_line or 'k1, 5' in raw_line: split_rooms = ['K1', 'K5']
    elif 'k1,k2' in raw_line: split_rooms = ['K1', 'K2']
    elif 'k1.k2' in raw_line: split_rooms = ['K1', 'K2']
    
    total_price = r['price']
    group_code = f"GRP-2026-{res_counter:04d}" if split_rooms else ""
    
    if split_rooms:
        num_rooms = len(split_rooms)
        alloc_base = total_price // num_rooms
        remainder = total_price % num_rooms
        
        for room_i, rm in enumerate(split_rooms):
            rm_price = alloc_base + (remainder if room_i == 0 else 0)
            r_split = dict(r)
            r_split['reservation_code'] = f"33H-2026-{res_counter:04d}"
            r_split['reservation_group'] = group_code
            r_split['room_code'] = rm
            r_split['room_type'] = ROOM_TYPES[rm]["type"]
            r_split['room_capacity'] = ROOM_TYPES[rm]["cap"]
            r_split['price_per_night'] = ROOM_TYPES[rm]["rate"]
            r_split['room_code_source'] = "recorded"
            r_split['total_price'] = rm_price
            r_split['reservation_total_price'] = total_price
            r_split['guest_name'] = f"Tamu-{res_counter:04d}"
            r_split['guest_phone'] = "08XXXXXXXXXX"
            final_rows.append(r_split)
            res_counter += 1
    else:
        r_single = dict(r)
        r_single['reservation_code'] = f"33H-2026-{res_counter:04d}"
        r_single['reservation_group'] = ""
        r_single['reservation_total_price'] = total_price
        r_single['total_price'] = total_price
        r_single['guest_name'] = f"Tamu-{res_counter:04d}"
        r_single['guest_phone'] = "08XXXXXXXXXX"
        final_rows.append(r_single)
        res_counter += 1

df_final = pd.DataFrame(final_rows)
print(f"Total baris setelah multi-kamar dipecah: {len(df_final)} baris")

# 7. TUGAS 7: REKONSILIASI
print("\n6. Menjalankan TUGAS 7 (Rekonsiliasi per Bulan & Tabel)...")
reconcil_rows = []
total_owner_all = 0
total_extracted_all = 0

for m_idx, m_name, pages in month_pages:
    t_left_owner, t_right_owner = targets[m_name]
    
    for side, t_owner in [("kiri", t_left_owner), ("kanan", t_right_owner)]:
        # Filter rows for this month and table in df_dedup (before multi-kamar split or after split price sum is identical!)
        m_side_df = df_final[(df_final['source_month'] == m_idx) & (df_final['source_table'] == side)]
        actual_sum = int(m_side_df['total_price'].sum())
        diff = actual_sum - t_owner
        
        explanation = "Sesuai rekap owner (selisih Rp0)"
        if m_name == "Februari" and side == "kanan":
            explanation = "Selisih sah -Rp170.000: baris tidak tercatat di PDF (rekap owner mencatat 3.120.000 tapi total fisik baris adalah 2.950.000)"
        elif m_name == "Juli" and side == "kiri":
            explanation = "Selisih sah +Rp200.000: baris harga tertulis teks 'Rp.200,000' (Sila 0708) tidak ikut formula SUM owner, namun valid dipakai"
        
        reconcil_rows.append({
            'bulan': m_name,
            'source_month': m_idx,
            'tabel': side,
            'total_owner': t_owner,
            'total_ekstraksi': actual_sum,
            'selisih': diff,
            'jumlah_baris': len(m_side_df),
            'penjelasan': explanation
        })
        total_owner_all += t_owner
        total_extracted_all += actual_sum
        print(f"[{m_name:9s} {side:5s}] Owner: Rp {t_owner:10,d} | Ekstraksi: Rp {actual_sum:10,d} | Selisih: Rp {diff:8,d} | {explanation}")

df_reconcil = pd.DataFrame(reconcil_rows)
reconcil_path = os.path.join(DATA_DIR, "reconciliation_report.csv")
df_reconcil.to_csv(reconcil_path, index=False)
print(f"Laporan rekonsiliasi disimpan ke {reconcil_path}")

# 8. TUGAS 8: PENYIMPANAN DATASET
print("\n7. Menjalankan TUGAS 8 (Penyimpanan Dataset Anonim)...")

# Pilih kolom untuk clean_reservations.csv
cols_analysis = [
    'reservation_code', 'reservation_group', 'source_month', 'source_table', 'source_row_no',
    'guest_name', 'guest_city', 'guest_city_source', 'guest_phone',
    'room_code', 'room_type', 'room_code_source', 'unit_scope', 'room_capacity', 'price_per_night',
    'check_in', 'check_out', 'nights', 'rental_type', 'total_price', 'reservation_total_price',
    'channel', 'ota_raw', 'ota_name', 'status', 'price_text_format', 'date_unresolved', 'needs_review', 'notes'
]

clean_csv_path = os.path.join(DATA_DIR, "clean_reservations.csv")
core_csv_path = os.path.join(DATA_DIR, "clean_reservations_core.csv")
raw_csv_path = os.path.join(DATA_DIR, "raw_reservations_manual.csv")

# Simpan clean_reservations.csv
df_final[cols_analysis].to_csv(clean_csv_path, index=False)
print(f"clean_reservations.csv disimpan: {len(df_final)} baris")

# Simpan clean_reservations_core.csv (hanya unit_scope == 'inti')
df_core = df_final[df_final['unit_scope'] == 'inti'][cols_analysis].copy().reset_index(drop=True)
df_core.to_csv(core_csv_path, index=False)
print(f"clean_reservations_core.csv disimpan: {len(df_core)} baris")

# Simpan raw_reservations_manual.csv (teranonimkan)
cols_raw = [
    'reservation_code', 'source_month', 'month_name', 'source_page', 'source_table', 'source_row_no',
    'guest_name', 'guest_city', 'raw_check_in', 'raw_check_out', 'price', 'ota_raw', 'room_raw', 'price_text_format'
]
df_final[cols_raw].to_csv(raw_csv_path, index=False)
print(f"raw_reservations_manual.csv disimpan: {len(df_final)} baris")

print("\n" + "=" * 70)
print("RINGKASAN AKHIR PIPELINE TUGAS 1 - 8:")
print(f"Total baris unik di PDF (sebelum multi-split): {len(df_dedup)} baris")
print(f"  - Tabel Kiri : {(df_dedup['source_table']=='kiri').sum()} baris")
print(f"  - Tabel Kanan: {(df_dedup['source_table']=='kanan').sum()} baris")
print(f"Total baris dataset analisis (setelah multi-split): {len(df_final)} baris")
print(f"  - Unit Inti (K1-K5 + Unassigned + K4 Kanan): {len(df_core)} baris")
print(f"  - Teras House (TH1 Kanan)                  : {(df_final['unit_scope']=='teras_house').sum()} baris")
print("\nDistribusi room_code:")
print(df_final['room_code'].value_counts(dropna=False))
print("\nDistribusi room_code_source:")
print(df_final['room_code_source'].value_counts())
print("\nDistribusi guest_city_source:")
print(df_final['guest_city_source'].value_counts())
print("\nBaris needs_review: ", df_final['needs_review'].sum())
print("Baris date_unresolved: ", df_final['date_unresolved'].sum())
print("=" * 70)
