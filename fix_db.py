import os, re

base = 'C:/xampp/htdocs/easybank/'

files = [
    'account_change_pass.php','account_settings.php','db.php','home.php',
    'i_code.php','i_code2.php','notifications.php',
    'setup.php','statics.php','test_connection.php','test_mysqli.php',
    'transac_deposits.php','transac_deposits_all.php','transac_export_all_banks.php',
    'transac_export_anyone_bank.php','transac_export_easy_bank.php','transac_withdrawals.php',
    'transf_anyone_bank.php','transf_anyone_bank_balance.php','transf_anyone_bank_check_recipient.php',
    'transf_anyone_bank_i_code.php','transf_anyone_bank_limit.php','transf_anyone_bank_send.php',
    'transf_anyone_bank_transac.php','transf_easy_bank.php','transf_easy_bank_balance.php',
    'transf_easy_bank_check_recipient.php','transf_easy_bank_i_code.php','transf_easy_bank_limit.php',
    'transf_easy_bank_send.php','transf_easy_bank_transac.php','update_tables.php'
]

# Pattern: $host/$user/$pass/$db assignments (with any whitespace/blank lines between)
# followed by new mysqli and error check
pattern = re.compile(
    r'\$host\s*=\s*\$obj_conn->connect\[0\]\s*;'
    r'\s*\$user\s*=\s*\$obj_conn->connect\[1\]\s*;'
    r'\s*\$pass\s*=\s*\$obj_conn->connect\[2\]\s*;'
    r'\s*\$db\s*=\s*\$obj_conn->connect\[3\]\s*;'
    r'\s*\$conn\s*=\s*new\s+mysqli\(\s*\$host\s*,\s*\$user\s*,\s*\$pass\s*,\s*\$db\s*\)\s*;'
    r'(\s*if\s*\(\s*\$conn->connect_error\s*\)[^;]+;)?',
    re.MULTILINE
)

for f in files:
    path = base + f
    if os.path.exists(path):
        with open(path, 'r', encoding='utf-8') as fh:
            content = fh.read()
        
        new_content = pattern.sub('$conn = $obj_conn->get_connection();', content)
        
        if new_content != content:
            with open(path, 'w', encoding='utf-8') as fh:
                fh.write(new_content)
            print(f'Fixed: {f}')
        else:
            print(f'No match: {f}')
    else:
        print(f'Not found: {f}')

print('Done!')