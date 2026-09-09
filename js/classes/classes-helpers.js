// Pure logic for the Classes page: filtering and section-grouping.
// Kept free of DOM access so it can be unit tested directly with Node.

export const ghtSectionOrder = ['Toddler', 'Nursery', 'KG', 'Primary', 'Secondary'];

// Returns classes matching the search term and section filter, excluding
// archived ones unless explicitly included.
export function ghtFilterClasses(ghtClasses, { ghtSearch = '', ghtSectionFilter = '', ghtIncludeArchived = false } = {}) {
  const ghtTerm = ghtSearch.trim().toLowerCase();
  return ghtClasses.filter((ghtClass) => {
    if (!ghtIncludeArchived && ghtClass.status === 'archived') return false;
    if (ghtSectionFilter && ghtClass.section !== ghtSectionFilter) return false;
    if (ghtTerm && !ghtClass.name.toLowerCase().includes(ghtTerm)) return false;
    return true;
  });
}

// Groups classes by section and orders sections using ghtSectionOrder first,
// then any unrecognized sections alphabetically after.
export function ghtGroupClassesBySection(ghtClasses) {
  const ghtGroups = {};
  ghtClasses.forEach((ghtClass) => {
    if (!ghtGroups[ghtClass.section]) ghtGroups[ghtClass.section] = [];
    ghtGroups[ghtClass.section].push(ghtClass);
  });
  const ghtKnownSections = ghtSectionOrder.filter((ghtSection) => ghtGroups[ghtSection]);
  const ghtOtherSections = Object.keys(ghtGroups)
    .filter((ghtSection) => !ghtSectionOrder.includes(ghtSection))
    .sort();
  return [...ghtKnownSections, ...ghtOtherSections].map((ghtSection) => [ghtSection, ghtGroups[ghtSection]]);
}

// Returns the distinct, sorted list of sections currently present in data —
// used to populate section filter dropdowns without hardcoding a fixed list.
export function ghtDistinctSections(ghtClasses) {
  return [...new Set(ghtClasses.map((ghtClass) => ghtClass.section))].sort();
}
