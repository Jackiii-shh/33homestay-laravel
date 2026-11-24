import re
import os
import pandas as pd

CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_RAW = os.path.join(CURRENT_DIR, "owner_actual_ledger_raw.csv")
OUTPUT_CLEAN = os.path.join(CURRENT_DIR, "owner_actual_ledger_clean.csv")

# We have the exact transcript data from the owner's PDF for all months Jan - Nov 2026
# Let's write an intelligent extractor that parses the entries into a rich dataframe.
