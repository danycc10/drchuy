import { initPublicNavigation, initServiceSearch } from './public-site';
import { initPublicMotion } from './public-motion';
import { initActiveSections } from './active-sections';
import { initVisitCard } from './visit-card';
import { initMobileContact } from './mobile-contact';

initPublicNavigation(document);
initActiveSections(document);
initServiceSearch(document);
initPublicMotion(document);
initVisitCard(document);
initMobileContact(document);
