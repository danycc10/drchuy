import { initPublicNavigation, initServiceSearch } from './public-site';
import { initPublicMotion } from './public-motion';
import { initActiveSections } from './active-sections';
import { initVisitCard } from './visit-card';

initPublicNavigation(document);
initActiveSections(document);
initServiceSearch(document);
initPublicMotion(document);
initVisitCard(document);
