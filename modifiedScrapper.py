import requests
from bs4 import BeautifulSoup
import csv
import json
import time

BASE_URL = 'https://ucsc.cmb.ac.lk/exam_results/'  # Replace with correct URL if different

def read_students_from_csv(csv_path):
    students = []
    with open(csv_path, newline='', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            if 'index' in row and 'nic' in row:
                students.append({'index': row['index'].strip(), 'nic': row['nic'].strip()})
    return students

def fetch_student_result(index, nic):
    payload = {
        'no': index,
        'pw': nic
    }

    with requests.Session() as session:
        response = session.post(BASE_URL, data=payload)
        soup = BeautifulSoup(response.text, 'html.parser')

        h5_tags = soup.find_all('h5')
        if not h5_tags:
            return None

        name = h5_tags[0].text.strip()
        index_text = h5_tags[1].text.strip() if len(h5_tags) > 1 else index

        results = []
        tables = soup.find_all('table')

        for table in tables:
            rows = table.find_all('tr')[1:]  # skip header row
            for row in rows:
                cols = row.find_all('td')
                if len(cols) >= 5:
                    results.append({
                        'subject': cols[0].text.strip(),
                        'year': cols[1].text.strip(),
                        'semester': cols[2].text.strip(),
                        'credits': cols[3].text.strip(),
                        'result': cols[4].text.strip()
                    })

        return {
            'name': name,
            'index': index_text,
            'results': results
        }

def main():
    students = read_students_from_csv('details.csv')
    all_results = []

    for student in students:
        print(f"Fetching for {student['index']}...")
        result = fetch_student_result(student['index'], student['nic'])
        if result:
            all_results.append(result)
        else:
            print(f"Failed to fetch for {student['index']}")
        time.sleep(1)  # Avoid overloading the server

    # Save JSON
    with open('all_student_results.json', 'w', encoding='utf-8') as f:
        json.dump(all_results, f, ensure_ascii=False, indent=2)
    print("Saved results to all_student_results.json")

    # Save CSV
    with open('all_student_results.csv', 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(['Name', 'Index', 'Subject', 'Year', 'Semester', 'Credits', 'Result'])
        for student in all_results:
            for res in student['results']:
                writer.writerow([
                    student['name'],
                    student['index'],
                    res['subject'],
                    res['year'],
                    res['semester'],
                    res['credits'],
                    res['result']
                ])
    print("Saved results to all_student_results.csv")

if __name__ == '__main__':
    main()
