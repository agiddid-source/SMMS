export const ghtNavigationConfig = [
  { ghtType: 'link', ghtLabel: 'Overview', ghtHref: './index.html', ghtIcon: 'grid' },
  { ghtType: 'link', ghtLabel: 'Ledger', ghtHref: './coming-soon.html?module=Ledger', ghtIcon: 'ledger' },
  { ghtType: 'group', ghtLabel: 'Classes & Fees', ghtIcon: 'school', ghtItems: [
    { ghtLabel: 'Classes', ghtHref: './classes.html' },
    { ghtLabel: 'Fee settings', ghtHref: './fee-setup.html' }
  ] },
  { ghtType: 'group', ghtLabel: 'Student Accounts', ghtIcon: 'users', ghtItems: [
    { ghtLabel: 'Student profiles', ghtHref: './student-financial-profile.html?student=sodiq-adeyemi' },
    { ghtLabel: 'Fee discounts', ghtHref: './student-financial-profile.html?student=sodiq-adeyemi#ght-concessions' }
  ] },
  { ghtType: 'group', ghtLabel: 'Bursar & Payments', ghtIcon: 'payment', ghtItems: [
    { ghtLabel: 'Invoices', ghtHref: './coming-soon.html?module=Invoicing%20%26%20Billing' },
    { ghtLabel: 'Payments', ghtHref: './coming-soon.html?module=Bursar%20%26%20Payments' }
  ] },
  { ghtType: 'group', ghtLabel: 'Expenses & Reports', ghtIcon: 'report', ghtItems: [
    { ghtLabel: 'Expenses', ghtHref: './coming-soon.html?module=Expenses' },
    { ghtLabel: 'Reports', ghtHref: './coming-soon.html?module=Reports' }
  ] },
  { ghtType: 'bottom', ghtLabel: 'Results', ghtHref: './coming-soon.html?module=Results', ghtIcon: 'chart' },
  { ghtType: 'bottom', ghtLabel: 'Staff', ghtHref: './coming-soon.html?module=Staff', ghtIcon: 'users' },
  { ghtType: 'bottom', ghtLabel: 'Payroll', ghtHref: './coming-soon.html?module=Payroll%20%26%20Payslips', ghtIcon: 'ledger' }
];
