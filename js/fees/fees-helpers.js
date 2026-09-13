// Pure logic for Fee Management: filtering the fee list and resolving
// assigned class IDs to display names. Kept free of DOM access so it can
// be unit tested directly with Node.

// Returns fees matching search/type/term filters, excluding inactive fees
// unless explicitly included.
export function ghtFilterFees(ghtFees, { ghtSearch = '', ghtTypeFilter = '', ghtTermFilter = '', ghtIncludeInactive = false } = {}) {
  const ghtTerm = ghtSearch.trim().toLowerCase();
  return ghtFees.filter((ghtFee) => {
    if (!ghtIncludeInactive && ghtFee.status === 'inactive') return false;
    if (ghtTypeFilter && ghtFee.type !== ghtTypeFilter) return false;
    if (ghtTermFilter && ghtFee.term !== ghtTermFilter) return false;
    if (ghtTerm && !ghtFee.name.toLowerCase().includes(ghtTerm)) return false;
    return true;
  });
}

// Resolves an array of class IDs to their display names, given the full
// class list. Silently drops IDs that no longer resolve to a class.
export function ghtResolveClassNames(ghtClassIds, ghtClasses) {
  return (ghtClassIds || [])
    .map((ghtId) => ghtClasses.find((ghtClass) => ghtClass.id === ghtId))
    .filter(Boolean)
    .map((ghtClass) => ghtClass.name);
}

// Returns the distinct, sorted list of terms present in the fee data — used
// to populate the term filter dropdown without hardcoding term names.
export function ghtDistinctTerms(ghtFees) {
  return [...new Set(ghtFees.map((ghtFee) => ghtFee.term))].sort();
}
