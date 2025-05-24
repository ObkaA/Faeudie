import csv

INPUT_FILE = 'produkt_bazowe.csv'
OUTPUT_FILE = 'produkty_all_unique_names.csv' # Changed output filename for clarity

def remove_duplicates_by_name(input_file, output_file):
    seen_names = set()
    unique_rows = []

    with open(input_file, mode='r', encoding='utf-8') as f:
        reader = csv.reader(f)
        header = next(reader) # Read the header row
        unique_rows.append(header) # Add header to unique rows

        # Assuming 'name' is the first column (index 0) in your CSV
        # If 'name' is in a different column, change this index accordingly.
        # For example, if it's the second column, use index 1: name_column_index = 1
        name_column_index = 0 

        for row in reader:
            if not row: # Skip empty rows
                continue

            # Get the name from the specified column
            name = row[name_column_index].strip().lower() # .strip() removes whitespace, .lower() for case-insensitive check

            if name not in seen_names:
                seen_names.add(name)
                unique_rows.append(row)

    with open(output_file, mode='w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerows(unique_rows)

    print(f"Usunięto duplikaty po nazwie. Zapisano {len(unique_rows)-1} unikalnych wierszy do {output_file}")

if __name__ == "__main__":
    remove_duplicates_by_name(INPUT_FILE, OUTPUT_FILE)
