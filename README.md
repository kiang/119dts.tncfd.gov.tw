# Tainan 119 DTS Table Crawler

This project is a PHP crawler that fetches and processes the emergency case table from [台南市119勤務派遣系統](https://119dts.tncfd.gov.tw/DTS/caselist/html). It extracts the table data and saves each case as a JSON file, organized by year and month, and also generates a complete list of all cases.

## Features
- Fetches the latest case table from the official DTS website
- Parses and extracts each row of the table
- Saves each case as a pretty-printed JSON file in `docs/{Y}/{m}/{編號}.json`
- Generates a complete list of all cases in `docs/list.json`
- Handles UTF-8 encoding for correct display of Chinese characters

## Usage

1. **Install PHP** (if not already installed)
2. Run the crawler script:
   ```bash
   php scripts/01_fetch.php
   ```
3. Output will be saved in the `docs/` directory:
   - `docs/list.json`: All cases in a single JSON array
   - `docs/{Y}/{m}/{編號}.json`: Each case as an individual JSON file, organized by year and month

## Output Structure

- `docs/list.json` (array of all cases):
  ```json
  [
    {
      "id": "130523",
      "datetime": "2025/05/15 13:05:23",
      "case_type": "緊急救護",
      "location": "台南市東區",
      "unit": "東門分隊",
      "status": "已到達"
    },
    ...
  ]
  ```
- `docs/2025/05/130523.json` (single case example):
  ```json
  {
    "id": "130523",
    "datetime": "2025/05/15 13:05:23",
    "case_type": "緊急救護",
    "location": "台南市東區",
    "unit": "東門分隊",
    "status": "已到達"
  }
  ```

## License

MIT License

Copyright (c) 2024 Finjon Kiang

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE. 