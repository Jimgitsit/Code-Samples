use alacarte-prod;
db.dropDatabase();
use alacarte-prod;
db.copyDatabase("alacarte-prod","alacarte-prod","localhost:27018");