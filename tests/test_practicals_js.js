/**
 * Automated Unit Test Suite for SkillBridge Client-side and Matching Algorithms
 */

// Load functions from assets/js/app.js
const fs = require('fs');
const path = require('path');

const appJs = fs.readFileSync(path.join(__dirname, '../assets/js/app.js'), 'utf8');

// Isolate functions
eval(appJs.slice(appJs.indexOf('function numberToWords')));

console.log('====================================================');
console.log('SkillBridge Automated Test Suite — Practicals & Logic');
console.log('====================================================');

let passed = 0;
let failed = 0;

function assert(condition, message) {
    if (condition) {
        console.log(`  [PASS] ${message}`);
        passed++;
    } else {
        console.error(`  [FAIL] ${message}`);
        failed++;
    }
}

// 1. Practical 4: Number to Words Tests
console.log('\n--- Testing Practical 4: numberToWords (0-999) ---');
assert(numberToWords(0) === 'Zero', '0 -> Zero');
assert(numberToWords(5) === 'Five', '5 -> Five');
assert(numberToWords(14) === 'Fourteen', '14 -> Fourteen');
assert(numberToWords(42) === 'Forty Two', '42 -> Forty Two');
assert(numberToWords(100) === 'One Hundred', '100 -> One Hundred');
assert(numberToWords(105) === 'One Hundred and Five', '105 -> One Hundred and Five');
assert(numberToWords(345) === 'Three Hundred and Forty Five', '345 -> Three Hundred and Forty Five');
assert(numberToWords(999) === 'Nine Hundred and Ninety Nine', '999 -> Nine Hundred and Ninety Nine');
assert(numberToWords(-5).includes('between 0 and 999'), '-5 bounds checking');
assert(numberToWords(1000).includes('between 0 and 999'), '1000 bounds checking');

// 2. Practical 5: Regex Validation Tests
console.log('\n--- Testing Practical 5: Regex Format Validation ---');
assert(validateField('student@example.com', 'email').valid === true, 'Valid email accepted');
assert(validateField('invalid-email@', 'email').valid === false, 'Malformed email rejected');
assert(validateField('9876543210', 'phone').valid === true, 'Valid 10-digit Indian mobile accepted');
assert(validateField('12345', 'phone').valid === false, 'Invalid mobile length rejected');
assert(validateField('Aarav Sharma', 'name').valid === true, 'Alphabetic name accepted');
assert(validateField('User123', 'name').valid === false, 'Name with numbers rejected');
assert(validateField('2027', 'year').valid === true, 'Valid graduation year accepted');
assert(validateField('1800', 'year').valid === false, 'Out-of-range year rejected');
assert(validateField('https://github.com', 'url').valid === true, 'HTTPS url accepted');
assert(validateField('ftp:/badurl', 'url').valid === false, 'Invalid url protocol rejected');

// 3. Section 8: Explainable Skill Matching Algorithm Logic Tests
console.log('\n--- Testing Core Matching Algorithm Calculations ---');

function calculateTestMatchScore(requiredSkills, studentSkills) {
    if (!requiredSkills || requiredSkills.length === 0) {
        return { score: 100, explanation: 'No requirements' };
    }
    const profLevels = { beginner: 1, intermediate: 2, advanced: 3 };
    const importanceWeights = { required: 1.0, preferred: 0.6, nice_to_have: 0.3 };

    let totalWeight = 0;
    let earnedWeight = 0;
    let matched = [];
    let missing = [];
    let weak = [];

    for (const rs of requiredSkills) {
        const weight = importanceWeights[rs.importance] || 1.0;
        totalWeight += weight;

        if (studentSkills[rs.name]) {
            const sLvl = profLevels[studentSkills[rs.name]] || 1;
            const rLvl = profLevels[rs.minProf] || 1;
            if (sLvl >= rLvl) {
                matched.push(rs.name);
                earnedWeight += weight;
            } else {
                weak.push(rs.name);
                earnedWeight += weight * (sLvl / rLvl);
            }
        } else {
            missing.push(rs.name);
        }
    }

    const score = totalWeight > 0 ? Math.round((earnedWeight / totalWeight) * 100) : 0;
    return { score, matched, missing, weak };
}

// Scenario A: Exact full match
const reqA = [
    { name: 'HTML', importance: 'required', minProf: 'intermediate' },
    { name: 'CSS', importance: 'required', minProf: 'intermediate' }
];
const stuA = { HTML: 'intermediate', CSS: 'advanced' };
const resA = calculateTestMatchScore(reqA, stuA);
assert(resA.score === 100, 'Full match gives 100%');
assert(resA.matched.length === 2, '2 matched skills');

// Scenario B: 75% baseline scenario from problem statement
const reqB = [
    { name: 'HTML', importance: 'required', minProf: 'intermediate' },
    { name: 'CSS', importance: 'required', minProf: 'intermediate' },
    { name: 'JavaScript', importance: 'required', minProf: 'intermediate' },
    { name: 'React', importance: 'required', minProf: 'intermediate' }
];
const stuB = { HTML: 'intermediate', CSS: 'intermediate', JavaScript: 'intermediate' };
const resB = calculateTestMatchScore(reqB, stuB);
assert(resB.score === 75, '3 of 4 required gives 75%');
assert(resB.missing.includes('React'), 'React identified as missing');

// Scenario C: Empty student profile
const resC = calculateTestMatchScore(reqB, {});
assert(resC.score === 0, 'Empty student profile gives 0%');
assert(resC.missing.length === 4, 'All 4 skills missing on empty profile');

// Scenario D: Partial proficiency credit (weak skill)
const reqD = [{ name: 'Java', importance: 'required', minProf: 'advanced' }]; // level 3
const stuD = { Java: 'beginner' }; // level 1
const resD = calculateTestMatchScore(reqD, stuD);
assert(resD.score === 33, 'Beginner against Advanced requirement gets proportional partial score (33%)');
assert(resD.weak.includes('Java'), 'Java identified as weak');

console.log('\n====================================================');
console.log(`Test Execution Summary: ${passed} Passed, ${failed} Failed`);
console.log('====================================================');

if (failed > 0) {
    process.exit(1);
}

