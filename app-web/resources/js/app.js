import { initPublicNavigation, initServiceSearch } from './public-site';
import { initPublicMotion } from './public-motion';
import { initActiveSections } from './active-sections';

initPublicNavigation(document);
initActiveSections(document);
initServiceSearch(document);
initPublicMotion(document);
